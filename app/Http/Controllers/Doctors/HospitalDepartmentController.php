<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Requests\HospitalDepartmentRequest;
use App\Models\Clinic;
use App\Models\HospitalBuilding;
use App\Models\Doctor;
use App\Models\EmployeeVacation;
use App\Models\HospitalBedAssignment;
use App\Models\HospitalDepartment;
use App\Models\Nurse;
use App\Support\HospitalDepartmentEligibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HospitalDepartmentController extends SettingsBaseController
{
    private const DAYS = [
        'mon' => 'ПН',
        'tue' => 'ВТ',
        'wed' => 'СР',
        'thu' => 'ЧТ',
        'fri' => 'ПТ',
        'sat' => 'СБ',
        'sun' => 'ВС',
    ];

    private const MODE_LABELS = [
        'day' => 'Дневной',
        'night' => 'Ночной',
        'round_the_clock' => 'Круглосуточный',
    ];

    private const SEX_TYPE_LABELS = [
        'male' => 'Мужское',
        'female' => 'Женское',
        'mixed' => 'Смешанное',
    ];

    public function index()
    {
        $clinic = $this->inpatientClinic();

        abort_unless(
            Schema::hasTable('hospital_departments'),
            503,
            'Таблица отделений стационара ещё не создана. Выполните миграции.'
        );

        abort_unless(
            Schema::hasTable('hospital_department_doctors'),
            503,
            'Таблица врачей отделений ещё не создана. Выполните миграции.'
        );

        $departments = HospitalDepartment::query()
            ->where('clinic_id', $clinic->id)
            ->with([
                'building:id,number,name,address',
                'headDoctor:id,name,surname,patronym,status,profession',
                'headNurse:id,name,surname,patronym,profession',
                'doctors:id,name,surname,patronym,status,profession',
            ])
            ->orderBy('sort_order')
            ->orderBy('number')
            ->orderBy('name')
            ->get();

        $currentVacationContext = $this->currentVacationContext(
            (int) $clinic->id
        );

        /*
         * Сотрудники амбулаторного контура не должны назначаться
         * в стационарное отделение. Берём фактические назначения
         * по кабинетам и участкам, а не пытаемся определять это
         * по названию должности.
         */
        $outpatientAssignments = $this->outpatientAssignments(
            (int) $clinic->id
        );

        /*
         * Постоянное отделение врача учитывается независимо от роли:
         * заведующий или обычный врач отделения.
         */
        $doctorAssignments = collect();

        foreach ($departments as $department) {
            if ($department->head_doctor_id) {
                $doctorAssignments->put(
                    (int) $department->head_doctor_id,
                    [
                        'department_id' => (int) $department->id,
                        'department_name' => (string) $department->name,
                        'role' => 'head',
                    ]
                );
            }

            foreach ($department->doctors as $departmentDoctor) {
                $doctorId = (int) $departmentDoctor->id;

                if ($doctorAssignments->has($doctorId)) {
                    continue;
                }

                $doctorAssignments->put(
                    $doctorId,
                    [
                        'department_id' => (int) $department->id,
                        'department_name' => (string) $department->name,
                        'role' => 'doctor',
                    ]
                );
            }
        }

        $nurseAssignments = $departments
            ->filter(
                static fn (HospitalDepartment $department): bool =>
                    !empty($department->head_nurse_id)
            )
            ->mapWithKeys(
                static fn (HospitalDepartment $department): array => [
                    (int) $department->head_nurse_id => [
                        'department_id' => (int) $department->id,
                        'department_name' => (string) $department->name,
                    ],
                ]
            );

        $clinicVisitingHours = $this->normalizeVisitingHours(
            $clinic->inpatient_visiting_hours ?? []
        );

        $departmentCards = $departments
            ->map(
                function (HospitalDepartment $department) use (
                    $clinicVisitingHours,
                    $currentVacationContext
                ): array {
                    $ownVisitingHours = $this->normalizeVisitingHours(
                        $department->visiting_hours ?? []
                    );

                    $effectiveVisitingHours =
                        $department->use_clinic_visiting_hours
                            ? $clinicVisitingHours
                            : $ownVisitingHours;

                    $buildingLabel = $department->building
                        ? trim(
                            'Корпус №'
                            . (string) (
                                $department->building->number
                                ?: $department->building->id
                            )
                            . (
                                $department->building->name
                                    ? ' — ' . $department->building->name
                                    : ''
                            )
                            . (
                                $department->building->address
                                    ? ' · ' . $department->building->address
                                    : ''
                            )
                        )
                        : 'Корпус не указан';

                    $doctorName = $this->employeeName(
                        $department->headDoctor
                    );

                    $nurseName = $this->employeeName(
                        $department->headNurse
                    );

                    $doctorVacation = $department->head_doctor_id
                        ? (
                            $currentVacationContext['doctor'][
                                (int) $department->head_doctor_id
                            ]
                            ?? null
                        )
                        : null;

                    $nurseVacation = $department->head_nurse_id
                        ? (
                            $currentVacationContext['nurse'][
                                (int) $department->head_nurse_id
                            ]
                            ?? null
                        )
                        : null;

                    $departmentDoctors = $department->doctors
                        ->map(
                            function (Doctor $doctor) use (
                                $currentVacationContext
                            ): array {
                                $vacation = $currentVacationContext['doctor'][
                                    (int) $doctor->id
                                ] ?? null;

                                return [
                                    'id' => (int) $doctor->id,
                                    'name' => $this->employeeName($doctor),
                                    'speciality' => trim(
                                        (string) (
                                            $doctor->status
                                            ?: $doctor->profession
                                            ?: 'Врач'
                                        )
                                    ),
                                    'on_vacation' => $vacation !== null,
                                    'vacation_label' =>
                                        $vacation['label'] ?? '',
                                    'replacement_name' =>
                                        $vacation['replacement_name'] ?? '',
                                ];
                            }
                        )
                        ->values()
                        ->all();

                    $ageGroupLabel = HospitalDepartmentEligibility::restrictions(
                        $department
                    )['label'] ?? 'Без возрастного ограничения';

                    return [
                        'id' => (int) $department->id,
                        'name' => (string) $department->name,
                        'number' => (string) ($department->number ?? ''),
                        'profile' => (string) ($department->profile ?? ''),
                        'age_group_label' => $ageGroupLabel,
                        'sex_type' => (string) (
                            $department->sex_type ?: 'mixed'
                        ),
                        'sex_type_label' => self::SEX_TYPE_LABELS[
                            (string) ($department->sex_type ?: 'mixed')
                        ] ?? 'Смешанное',
                        'description' => (string) ($department->description ?? ''),
                        'enabled' => (bool) $department->enabled,
                        'building_label' => $buildingLabel,
                        'building_address' => (string) (
                            $department->building?->address ?? ''
                        ),
                        'head_doctor_name' => $doctorName,
                        'head_doctor_on_vacation' =>
                            $doctorVacation !== null,
                        'head_doctor_vacation_label' =>
                            $doctorVacation['label'] ?? '',
                        'acting_head_doctor_name' =>
                            $doctorVacation['replacement_name'] ?? '',
                        'head_nurse_name' => $nurseName,
                        'head_nurse_on_vacation' =>
                            $nurseVacation !== null,
                        'head_nurse_vacation_label' =>
                            $nurseVacation['label'] ?? '',
                        'acting_head_nurse_name' =>
                            $nurseVacation['replacement_name'] ?? '',
                        'doctors' => $departmentDoctors,
                        'doctors_count' => count($departmentDoctors),
                        'mode' => (string) $department->inpatient_mode,
                        'mode_label' => self::MODE_LABELS[
                            (string) $department->inpatient_mode
                        ] ?? 'Не указан',
                        'use_clinic_visiting_hours' =>
                            (bool) $department->use_clinic_visiting_hours,
                        'visiting_source_label' =>
                            $department->use_clinic_visiting_hours
                                ? 'Общий график клиники'
                                : 'Собственный график отделения',
                        'visiting_hours_label' =>
                            $this->formatVisitingHours(
                                $effectiveVisitingHours
                            ),
                        'search_text' => mb_strtolower(
                            implode(' ', [
                                (string) $department->number,
                                (string) $department->name,
                                (string) $department->profile,
                                $ageGroupLabel,
                                self::SEX_TYPE_LABELS[
                                    (string) ($department->sex_type ?: 'mixed')
                                ] ?? 'Смешанное',
                                $buildingLabel,
                                $doctorName,
                                (string) ($doctorVacation['replacement_name'] ?? ''),
                                $nurseName,
                                (string) ($nurseVacation['replacement_name'] ?? ''),
                                implode(
                                    ' ',
                                    array_column(
                                        $departmentDoctors,
                                        'name'
                                    )
                                ),
                            ])
                        ),
                    ];
                }
            )
            ->values()
            ->all();

        $departmentForms = $departments
            ->mapWithKeys(
                function (HospitalDepartment $department): array {
                    return [
                        (string) $department->id => [
                            'id' => (int) $department->id,
                            'name' => (string) $department->name,
                            'number' => (string) ($department->number ?? ''),
                            'profile' => (string) ($department->profile ?? ''),
                            'patient_age_group' =>
                                HospitalDepartmentEligibility::ageGroup($department)
                                ?? 'general',
                            'sex_type' => (string) (
                                $department->sex_type ?: 'mixed'
                            ),
                            'building_id' => $department->building_id
                                ? (int) $department->building_id
                                : null,
                            'head_doctor_id' => $department->head_doctor_id
                                ? (int) $department->head_doctor_id
                                : null,
                            'head_nurse_id' => $department->head_nurse_id
                                ? (int) $department->head_nurse_id
                                : null,
                            'doctor_ids' => $department->doctors
                                ->pluck('id')
                                ->map(
                                    static fn ($id): int => (int) $id
                                )
                                ->values()
                                ->all(),
                            'inpatient_mode' => (string) (
                                $department->inpatient_mode
                                ?: 'round_the_clock'
                            ),
                            'use_clinic_visiting_hours' =>
                                (bool) $department->use_clinic_visiting_hours,
                            'visiting_hours' =>
                                $this->normalizeVisitingHours(
                                    $department->visiting_hours ?? []
                                ),
                            'description' =>
                                (string) ($department->description ?? ''),
                            'enabled' => (bool) $department->enabled,
                            'sort_order' => (int) $department->sort_order,
                        ],
                    ];
                }
            )
            ->all();

        abort_unless(
            Schema::hasTable('hospital_buildings'),
            503,
            'Таблица корпусов стационара ещё не создана. Выполните миграции.'
        );

        $allBuildings = HospitalBuilding::query()
            ->where('clinic_id', $clinic->id)
            ->withCount('departments')
            ->orderBy('number')
            ->orderBy('name')
            ->get();

        $buildings = $allBuildings
            ->where('enabled', true)
            ->values();

        /*
         * В стационарных селекторах вообще не показываем сотрудников
         * амбулаторного контура. Это именно разделение контуров, а не
         * визуальная блокировка: сотрудника диспансера здесь нет даже
         * среди результатов поиска.
         *
         * Важный момент: outpatientAssignments() учитывает и отключённые
         * кабинеты/участки. Во время отпуска карточка кабинета может быть
         * временно деактивирована, но врач от этого не перестаёт быть
         * сотрудником амбулаторного контура.
         */
        $outpatientDoctorIds = array_map(
            'intval',
            array_keys($outpatientAssignments['doctor'])
        );

        $outpatientNurseIds = array_map(
            'intval',
            array_keys($outpatientAssignments['nurse'])
        );

        /*
         * Для редактирования обязательно оставляем в option-ах сотрудников,
         * которые УЖЕ закреплены за стационаром. Это важно и для старых
         * данных, где могла сохраниться параллельная амбулаторная привязка.
         * Иначе departmentForms содержит id, но поисковый селектор не
         * находит option и очищает поле при открытии модального окна.
         *
         * В новых назначениях разделение контуров всё равно сохраняется:
         * legacy-пересечения скрываются во всех чужих отделениях, а backend
         * запрещает создать новое пересечение стационар/амбулатория.
         */
        $hospitalDoctorIds = $doctorAssignments
            ->keys()
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        $hospitalNurseIds = $nurseAssignments
            ->keys()
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        $outpatientDoctorIdsToExclude = array_values(
            array_diff(
                $outpatientDoctorIds,
                $hospitalDoctorIds
            )
        );

        $outpatientNurseIdsToExclude = array_values(
            array_diff(
                $outpatientNurseIds,
                $hospitalNurseIds
            )
        );

        $doctors = Doctor::query()
            ->where('clinic_id', $clinic->id)
            ->when(
                $outpatientDoctorIdsToExclude !== [],
                static fn ($query) =>
                    $query->whereNotIn(
                        'id',
                        $outpatientDoctorIdsToExclude
                    )
            )
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('profession')
                        ->orWhereNotIn(
                            'profession',
                            [
                                'Старшая медицинская сестра',
                                'Медицинская сестра',
                                'Процедурная медицинская сестра',
                                'Санитар',
                                'Медицинский регистратор',
                            ]
                        );
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'surname',
                'patronym',
                'status',
                'profession',
            ]);

        $nurses = Nurse::query()
            ->where('clinic_id', $clinic->id)
            ->when(
                $outpatientNurseIdsToExclude !== [],
                static fn ($query) =>
                    $query->whereNotIn(
                        'id',
                        $outpatientNurseIdsToExclude
                    )
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'surname',
                'patronym',
                'profession',
            ]);

        /*
         * При редактировании уже назначенные сотрудники должны оставаться
         * доступными в поисковых селекторах даже если они не попали в
         * основной запрос доступных сотрудников (например, из-за старой
         * привязки или изменения кадровых данных).
         *
         * Это не разрешает назначать сотрудника в другое отделение:
         * доступность по-прежнему проверяется и JS, и Request.
         */
        $assignedDoctors = $departments
            ->flatMap(
                static function (HospitalDepartment $department) {
                    return collect([
                        $department->headDoctor,
                    ])->concat($department->doctors);
                }
            )
            ->filter()
            ->unique('id')
            ->values();

        $assignedNurses = $departments
            ->pluck('headNurse')
            ->filter()
            ->unique('id')
            ->values();

        $doctors = $doctors
            ->concat($assignedDoctors)
            ->unique('id')
            ->sortBy(
                static fn (Doctor $doctor): string =>
                    mb_strtolower(
                        trim(
                            implode(' ', array_filter([
                                $doctor->surname,
                                $doctor->name,
                                $doctor->patronym,
                            ]))
                        )
                    )
            )
            ->values();

        $nurses = $nurses
            ->concat($assignedNurses)
            ->unique('id')
            ->sortBy(
                static fn (Nurse $nurse): string =>
                    mb_strtolower(
                        trim(
                            implode(' ', array_filter([
                                $nurse->surname,
                                $nurse->name,
                                $nurse->patronym,
                            ]))
                        )
                    )
            )
            ->values();

        $buildingOptions = $buildings
            ->map(
                static function (HospitalBuilding $building): array {
                    return [
                        'id' => (int) $building->id,
                        'label' => trim(
                            'Корпус №'
                            . (string) ($building->number ?: $building->id)
                            . (
                                $building->name
                                    ? ' — ' . $building->name
                                    : ''
                            )
                            . (
                                $building->address
                                    ? ' · ' . $building->address
                                    : ''
                            )
                        ),
                    ];
                }
            )
            ->values()
            ->all();

        $buildingCards = $allBuildings
            ->map(
                static function (HospitalBuilding $building): array {
                    return [
                        'id' => (int) $building->id,
                        'number' => (string) $building->number,
                        'name' => (string) $building->name,
                        'address' => (string) ($building->address ?? ''),
                        'enabled' => (bool) $building->enabled,
                        'departments_count' =>
                            (int) $building->departments_count,
                    ];
                }
            )
            ->values()
            ->all();

        $buildingForms = collect($buildingCards)
            ->mapWithKeys(
                static fn (array $building): array => [
                    (string) $building['id'] => $building,
                ]
            )
            ->all();

        $doctorOptions = $doctors
            ->map(
                function (Doctor $doctor) use (
                    $doctorAssignments,
                    $currentVacationContext,
                    $outpatientAssignments
                ): array {
                    $name = $this->employeeName($doctor);
                    $speciality = trim((string) ($doctor->status ?? ''));
                    $profession = trim(
                        (string) ($doctor->profession ?? '')
                    );
                    $subtitle = $speciality !== ''
                        ? $speciality
                        : ($profession !== '' ? $profession : 'Врач');

                    $assignment = $doctorAssignments->get(
                        (int) $doctor->id
                    );
                    $vacation = $currentVacationContext['doctor'][
                        (int) $doctor->id
                    ] ?? null;

                    $outpatientAssignment =
                        $outpatientAssignments['doctor'][
                            (int) $doctor->id
                        ] ?? '';

                    return [
                        'id' => (int) $doctor->id,
                        'name' => $name,
                        'subtitle' => $subtitle,
                        'label' => $name . ' — ' . $subtitle,
                        'search' => mb_strtolower(
                            trim($name . ' ' . $subtitle)
                        ),
                        'assigned_department_id' =>
                            $assignment['department_id'] ?? null,
                        'assigned_department_name' =>
                            $assignment['department_name'] ?? '',
                        'on_vacation' => $vacation !== null,
                        'vacation_label' => $vacation['label'] ?? '',
                        'replacement_name' =>
                            $vacation['replacement_name'] ?? '',
                        'outpatient' =>
                            $outpatientAssignment !== '',
                        'outpatient_assignment' =>
                            $outpatientAssignment,
                    ];
                }
            )
            ->values()
            ->all();

        $nurseOptions = $nurses
            ->map(
                function (Nurse $nurse) use (
                    $nurseAssignments,
                    $currentVacationContext,
                    $outpatientAssignments
                ): array {
                    $name = $this->employeeName($nurse);
                    $profession = trim(
                        (string) ($nurse->profession ?? '')
                    );
                    $subtitle = $profession !== ''
                        ? $profession
                        : 'Медицинская сестра';

                    $assignment = $nurseAssignments->get(
                        (int) $nurse->id
                    );
                    $vacation = $currentVacationContext['nurse'][
                        (int) $nurse->id
                    ] ?? null;

                    $outpatientAssignment =
                        $outpatientAssignments['nurse'][
                            (int) $nurse->id
                        ] ?? '';

                    return [
                        'id' => (int) $nurse->id,
                        'name' => $name,
                        'subtitle' => $subtitle,
                        'label' => $name . ' — ' . $subtitle,
                        'search' => mb_strtolower(
                            trim($name . ' ' . $subtitle)
                        ),
                        'assigned_department_id' =>
                            $assignment['department_id'] ?? null,
                        'assigned_department_name' =>
                            $assignment['department_name'] ?? '',
                        'on_vacation' => $vacation !== null,
                        'vacation_label' => $vacation['label'] ?? '',
                        'replacement_name' =>
                            $vacation['replacement_name'] ?? '',
                        'outpatient' =>
                            $outpatientAssignment !== '',
                        'outpatient_assignment' =>
                            $outpatientAssignment,
                    ];
                }
            )
            ->values()
            ->all();

        $stats = [
            'total' => $departments->count(),
            'active' => $departments
                ->where('enabled', true)
                ->count(),
            'round_the_clock' => $departments
                ->where('inpatient_mode', 'round_the_clock')
                ->where('enabled', true)
                ->count(),
            'custom_visiting' => $departments
                ->where('use_clinic_visiting_hours', false)
                ->count(),
        ];

        return $this->settingsView(
            'doctors.settings.departments',
            [
                'clinic' => $clinic,
                'departmentCards' => $departmentCards,
                'departmentForms' => $departmentForms,
                'buildingOptions' => $buildingOptions,
                'buildingCards' => $buildingCards,
                'buildingForms' => $buildingForms,
                'doctorOptions' => $doctorOptions,
                'nurseOptions' => $nurseOptions,
                'visitingDays' => self::DAYS,
                'stats' => $stats,
                'clinicVisitingHoursLabel' =>
                    $this->formatVisitingHours(
                        $clinicVisitingHours
                    ),
                'departmentStoreUrl' => url(
                    '/doctors/settings/departments'
                ),
                'departmentUpdateUrlTemplate' => url(
                    '/doctors/settings/departments/__DEPARTMENT__'
                ),
                'departmentDeleteUrlTemplate' => url(
                    '/doctors/settings/departments/__DEPARTMENT__/delete'
                ),
                'buildingStoreUrl' => url(
                    '/doctors/settings/departments/buildings'
                ),
                'buildingUpdateUrl' => url(
                    '/doctors/settings/departments/buildings/update'
                ),
                'buildingDeleteUrl' => url(
                    '/doctors/settings/departments/buildings/delete'
                ),
                'buildingAddressSearchUrl' => route(
                    'api-search-address'
                ),
            ]
        );
    }

    public function store(
        HospitalDepartmentRequest $request
    ): RedirectResponse {
        $clinic = $this->inpatientClinic();
        $data = $request->validated();

        DB::transaction(
            function () use ($clinic, $data): void {
                $department = HospitalDepartment::create(
                    $this->payload(
                        $clinic,
                        $data
                    )
                );

                $this->syncDepartmentDoctors(
                    $department,
                    $data
                );
            }
        );

        return back()->with(
            'success',
            'Отделение добавлено.'
        );
    }

    public function update(
        HospitalDepartmentRequest $request,
        HospitalDepartment $department
    ): RedirectResponse {
        $clinic = $this->inpatientClinic();
        $this->ensureDepartmentBelongsToClinic(
            $department,
            $clinic
        );

        $data = $request->validated();

        DB::transaction(
            function () use (
                $department,
                $clinic,
                $data
            ): void {
                $lockedDepartment = HospitalDepartment::query()
                    ->where('clinic_id', $clinic->id)
                    ->lockForUpdate()
                    ->findOrFail($department->id);

                $payload = $this->payload(
                    $clinic,
                    $data,
                    $lockedDepartment
                );
                $candidate = clone $lockedDepartment;
                $candidate->fill($payload);

                $this->assertAgeGroupChangeIsEligible(
                    $lockedDepartment,
                    $candidate,
                    (int) $clinic->id
                );

                $lockedDepartment->update($payload);

                $this->syncDepartmentDoctors(
                    $lockedDepartment,
                    $data
                );
            },
            3
        );

        return back()->with(
            'success',
            'Отделение обновлено.'
        );
    }

    public function destroy(
        HospitalDepartment $department
    ): RedirectResponse {
        $clinic = $this->inpatientClinic();
        $this->ensureDepartmentBelongsToClinic(
            $department,
            $clinic
        );

        $name = (string) $department->name;
        $department->delete();

        return back()->with(
            'success',
            'Отделение «' . $name . '» удалено.'
        );
    }


    public function storeBuilding(
        Request $request
    ): RedirectResponse {
        $clinic = $this->inpatientClinic();
        $clinicId = (int) $clinic->id;

        $data = $request->validateWithBag(
            'hospitalBuilding',
            [
                'building_number' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        'hospital_buildings',
                        'number'
                    )->where(
                        'clinic_id',
                        $clinicId
                    ),
                ],
                'building_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique(
                        'hospital_buildings',
                        'name'
                    )->where(
                        'clinic_id',
                        $clinicId
                    ),
                ],
                'building_address' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'building_number.required' =>
                    'Укажите номер корпуса стационара.',
                'building_number.unique' =>
                    'Корпус стационара с таким номером уже существует.',
                'building_name.required' =>
                    'Укажите название корпуса стационара.',
                'building_name.unique' =>
                    'Корпус стационара с таким названием уже существует.',
                'building_address.max' =>
                    'Адрес корпуса не должен превышать 255 символов.',
            ]
        );

        HospitalBuilding::create([
            'clinic_id' => $clinicId,
            'number' => trim((string) $data['building_number']),
            'name' => trim((string) $data['building_name']),
            'address' => trim(
                (string) ($data['building_address'] ?? '')
            ) ?: null,
            'enabled' => true,
        ]);

        return back()->with(
            'success',
            'Корпус стационара добавлен.'
        );
    }

    public function updateBuilding(
        Request $request
    ): RedirectResponse {
        $clinic = $this->inpatientClinic();
        $clinicId = (int) $clinic->id;
        $buildingId = $request->integer('building_id');

        $building = HospitalBuilding::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail($buildingId);

        $data = $request->validateWithBag(
            'hospitalBuilding',
            [
                'building_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'hospital_buildings',
                        'id'
                    )->where(
                        'clinic_id',
                        $clinicId
                    ),
                ],
                'building_number' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        'hospital_buildings',
                        'number'
                    )
                        ->where(
                            'clinic_id',
                            $clinicId
                        )
                        ->ignore($building->id),
                ],
                'building_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique(
                        'hospital_buildings',
                        'name'
                    )
                        ->where(
                            'clinic_id',
                            $clinicId
                        )
                        ->ignore($building->id),
                ],
                'building_address' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'building_enabled' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'building_id.required' =>
                    'Не удалось определить корпус стационара.',
                'building_id.exists' =>
                    'Корпус стационара не относится к текущей клинике.',
                'building_number.required' =>
                    'Укажите номер корпуса стационара.',
                'building_number.unique' =>
                    'Корпус стационара с таким номером уже существует.',
                'building_name.required' =>
                    'Укажите название корпуса стационара.',
                'building_name.unique' =>
                    'Корпус стационара с таким названием уже существует.',
                'building_address.max' =>
                    'Адрес корпуса не должен превышать 255 символов.',
            ]
        );

        $building->update([
            'number' => trim((string) $data['building_number']),
            'name' => trim((string) $data['building_name']),
            'address' => trim(
                (string) ($data['building_address'] ?? '')
            ) ?: null,
            'enabled' => array_key_exists(
                'building_enabled',
                $data
            )
                ? (bool) $data['building_enabled']
                : true,
        ]);

        return back()->with(
            'success',
            'Корпус стационара обновлён.'
        );
    }

    public function deleteBuilding(
        Request $request
    ): RedirectResponse {
        $clinic = $this->inpatientClinic();
        $clinicId = (int) $clinic->id;
        $buildingId = $request->integer('building_id');

        $building = HospitalBuilding::query()
            ->where('clinic_id', $clinicId)
            ->withCount('departments')
            ->findOrFail($buildingId);

        if ($building->departments_count > 0) {
            return back()->withErrors(
                [
                    'building_id' =>
                        'Нельзя удалить корпус, пока к нему привязаны отделения стационара.',
                ],
                'hospitalBuilding'
            );
        }

        $name = trim(
            'Корпус №'
            . (string) $building->number
            . (
                $building->name
                    ? ' — ' . $building->name
                    : ''
            )
        );

        $building->delete();

        return back()->with(
            'success',
            $name . ' удалён.'
        );
    }

    private function inpatientClinic(): Clinic
    {
        $doctor = auth()->user()?->doctor;

        abort_unless(
            $doctor && $doctor->clinic_id,
            403
        );

        $clinic = Clinic::findOrFail(
            (int) $doctor->clinic_id
        );

        abort_unless(
            (string) $clinic->clinic_type === 'inpatient',
            404
        );

        return $clinic;
    }

    private function ensureDepartmentBelongsToClinic(
        HospitalDepartment $department,
        Clinic $clinic
    ): void {
        abort_unless(
            (int) $department->clinic_id
                === (int) $clinic->id,
            404
        );
    }

    private function payload(
        Clinic $clinic,
        array $data,
        ?HospitalDepartment $department = null
    ): array {
        $useClinicVisitingHours = (bool) (
            $data['use_clinic_visiting_hours']
            ?? true
        );

        return [
            'clinic_id' => (int) $clinic->id,
            'building_id' => !empty($data['building_id'])
                ? (int) $data['building_id']
                : null,
            'head_doctor_id' => !empty($data['head_doctor_id'])
                ? (int) $data['head_doctor_id']
                : null,
            'head_nurse_id' => !empty($data['head_nurse_id'])
                ? (int) $data['head_nurse_id']
                : null,
            'name' => trim((string) $data['name']),
            'number' => trim((string) ($data['number'] ?? '')) ?: null,
            'profile' => trim((string) ($data['profile'] ?? '')) ?: null,
            'patient_age_group' => array_key_exists('patient_age_group', $data)
                ? $data['patient_age_group']
                : ($department?->getAttributes()['patient_age_group'] ?? null),
            'sex_type' => (string) (
                $data['sex_type']
                ?? HospitalDepartment::SEX_TYPE_MIXED
            ),
            'inpatient_mode' => (string) $data['inpatient_mode'],
            'use_clinic_visiting_hours' => $useClinicVisitingHours,
            'visiting_hours' => $useClinicVisitingHours
                ? null
                : $this->normalizeVisitingHours(
                    $data['visiting_hours'] ?? []
                ),
            'description' => trim(
                (string) ($data['description'] ?? '')
            ) ?: null,
            'enabled' => (bool) ($data['enabled'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function assertAgeGroupChangeIsEligible(
        HospitalDepartment $department,
        HospitalDepartment $candidate,
        int $clinicId
    ): void {
        $newGroup = HospitalDepartmentEligibility::ageGroup($candidate);
        if (
            $newGroup === null
            || $newGroup === HospitalDepartmentEligibility::ageGroup($department)
        ) {
            return;
        }

        $assignments = HospitalBedAssignment::query()
            ->whereNull('released_at')
            ->whereHas('bed', fn (Builder $bed): Builder => $bed
                ->where('clinic_id', $clinicId)
                ->whereHas('room', fn (Builder $room): Builder => $room
                    ->where('clinic_id', $clinicId)
                    ->where('department_id', $department->id)))
            ->with('patient')
            ->get();

        foreach ($assignments as $assignment) {
            $violation = $assignment->patient
                ? HospitalDepartmentEligibility::violation($assignment->patient, $candidate)
                : 'В одном из размещений отсутствуют данные пациента.';
            if ($violation !== null) {
                throw ValidationException::withMessages([
                    'patient_age_group' => 'Нельзя изменить возрастную группу отделения. ' . $violation,
                ]);
            }
        }
    }

    private function syncDepartmentDoctors(
        HospitalDepartment $department,
        array $data
    ): void {
        $doctorIds = collect($data['doctor_ids'] ?? [])
            ->map(
                static fn ($id): int => (int) $id
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        $department->doctors()->sync($doctorIds);
    }

    private function normalizeVisitingHours(
        mixed $value
    ): array {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            $value = [];
        }

        $result = [];

        foreach (self::DAYS as $code => $label) {
            $row = isset($value[$code]) && is_array($value[$code])
                ? $value[$code]
                : [];

            $result[$code] = [
                'enabled' => (bool) ($row['enabled'] ?? false),
                'from' => !empty($row['from'])
                    ? substr((string) $row['from'], 0, 5)
                    : '',
                'to' => !empty($row['to'])
                    ? substr((string) $row['to'], 0, 5)
                    : '',
            ];
        }

        return $result;
    }

    private function formatVisitingHours(
        array $hours
    ): string {
        $items = [];

        foreach (self::DAYS as $code => $label) {
            $row = $hours[$code] ?? [];

            if (empty($row['enabled'])) {
                continue;
            }

            $from = (string) ($row['from'] ?? '');
            $to = (string) ($row['to'] ?? '');

            if ($from === '' || $to === '') {
                continue;
            }

            $items[] = $label . ' ' . $from . '–' . $to;
        }

        return $items
            ? implode(' · ', $items)
            : 'Посещения не разрешены';
    }

    private function outpatientAssignments(
        int $clinicId
    ): array {
        $result = [
            'doctor' => [],
            'nurse' => [],
        ];

        /*
         * Основные назначения в амбулаторных кабинетах.
         * Замещающих здесь намеренно не считаем постоянным местом работы:
         * замещение может быть временным и не должно навсегда закрывать
         * сотруднику возможность быть оформленным в стационаре.
         */
        if (Schema::hasTable('clinic_cabinets')) {
            $cabinets = DB::table('clinic_cabinets')
                ->where('clinic_id', $clinicId)
                ->get([
                    'id',
                    'cabinet',
                    'name',
                    'doctor_id',
                    'nurse_id',
                ]);

            foreach ($cabinets as $cabinet) {
                $cabinetLabel = trim(
                    'Амбулатория · '
                    . (
                        !empty($cabinet->cabinet)
                            ? 'кабинет №' . $cabinet->cabinet
                            : (
                                !empty($cabinet->name)
                                    ? (string) $cabinet->name
                                    : 'кабинет #' . $cabinet->id
                            )
                    )
                );

                if (!empty($cabinet->doctor_id)) {
                    $result['doctor'][(int) $cabinet->doctor_id] =
                        $cabinetLabel;
                }

                if (!empty($cabinet->nurse_id)) {
                    $result['nurse'][(int) $cabinet->nurse_id] =
                        $cabinetLabel;
                }
            }
        }

        /*
         * Участковые и выездные врачи относятся к амбулаторному контуру.
         */
        if (Schema::hasTable('clinic_districts')) {
            $districts = DB::table('clinic_districts')
                ->where('clinic_id', $clinicId)
                ->get([
                    'id',
                    'name',
                    'doctor_id',
                    'home_visit_doctor_id',
                ]);

            foreach ($districts as $district) {
                $districtLabel =
                    'Амбулатория · участок «'
                    . (
                        !empty($district->name)
                            ? (string) $district->name
                            : '#' . $district->id
                    )
                    . '»';

                foreach ([
                    $district->doctor_id ?? null,
                    $district->home_visit_doctor_id ?? null,
                ] as $doctorId) {
                    if (!empty($doctorId)) {
                        $result['doctor'][(int) $doctorId] =
                            $districtLabel;
                    }
                }
            }

            if (Schema::hasTable('clinic_district_doctors')) {
                $additionalDistrictDoctors = DB::table(
                    'clinic_district_doctors as cdd'
                )
                    ->join(
                        'clinic_districts as cd',
                        'cd.id',
                        '=',
                        'cdd.district_id'
                    )
                    ->where('cdd.clinic_id', $clinicId)
                    ->where('cd.clinic_id', $clinicId)
                    ->get([
                        'cdd.doctor_id',
                        'cd.id as district_id',
                        'cd.name as district_name',
                    ]);

                foreach (
                    $additionalDistrictDoctors
                    as $assignment
                ) {
                    if (empty($assignment->doctor_id)) {
                        continue;
                    }

                    $result['doctor'][
                        (int) $assignment->doctor_id
                    ] =
                        'Амбулатория · участок «'
                        . (
                            !empty($assignment->district_name)
                                ? (string) $assignment->district_name
                                : '#' . $assignment->district_id
                        )
                        . '»';
                }
            }
        }

        return $result;
    }

    private function currentVacationContext(
        int $clinicId
    ): array {
        $result = [
            'doctor' => [],
            'nurse' => [],
        ];

        if (!Schema::hasTable('employee_vacations')) {
            return $result;
        }

        $today = Carbon::today();

        $vacations = EmployeeVacation::query()
            ->where('clinic_id', $clinicId)
            ->where('status', 'approved')
            ->whereDate(
                'date_from',
                '<=',
                $today->toDateString()
            )
            ->whereDate(
                'date_to',
                '>=',
                $today->toDateString()
            )
            ->get();

        if ($vacations->isEmpty()) {
            return $result;
        }

        $replacementDoctorIds = $vacations
            ->where('employee_type', 'doctor')
            ->pluck('replace_doctor_id')
            ->filter()
            ->map(
                static fn ($id): int => (int) $id
            )
            ->unique()
            ->values();

        $replacementNurseIds = $vacations
            ->where('employee_type', 'nurse')
            ->pluck('replace_nurse_id')
            ->filter()
            ->map(
                static fn ($id): int => (int) $id
            )
            ->unique()
            ->values();

        $replacementDoctors = $replacementDoctorIds->isEmpty()
            ? collect()
            : Doctor::query()
                ->where('clinic_id', $clinicId)
                ->whereIn('id', $replacementDoctorIds)
                ->get([
                    'id',
                    'name',
                    'surname',
                    'patronym',
                ])
                ->keyBy('id');

        $replacementNurses = $replacementNurseIds->isEmpty()
            ? collect()
            : Nurse::query()
                ->where('clinic_id', $clinicId)
                ->whereIn('id', $replacementNurseIds)
                ->get([
                    'id',
                    'name',
                    'surname',
                    'patronym',
                ])
                ->keyBy('id');

        foreach ($vacations as $vacation) {
            $employeeType = (string) $vacation->employee_type;

            if (!array_key_exists($employeeType, $result)) {
                continue;
            }

            $employeeId = (int) $vacation->employee_id;

            if ($employeeId <= 0) {
                continue;
            }

            $dateTo = Carbon::parse(
                (string) $vacation->date_to
            );

            $replacementName = '';

            if ($employeeType === 'doctor') {
                $replacementId = (int) (
                    $vacation->replace_doctor_id
                    ?? 0
                );

                $replacementName = $replacementId > 0
                    ? $this->employeeName(
                        $replacementDoctors->get(
                            $replacementId
                        )
                    )
                    : '';
            } else {
                $replacementId = (int) (
                    $vacation->replace_nurse_id
                    ?? 0
                );

                $replacementName = $replacementId > 0
                    ? $this->employeeName(
                        $replacementNurses->get(
                            $replacementId
                        )
                    )
                    : '';
            }

            $result[$employeeType][$employeeId] = [
                'label' =>
                    'В отпуске до '
                    . $dateTo->format('d.m.Y'),
                'replacement_name' =>
                    $replacementName,
            ];
        }

        return $result;
    }

    private function employeeName($employee): string
    {
        if (!$employee) {
            return '';
        }

        return trim(
            implode(
                ' ',
                array_filter([
                    $employee->surname ?? null,
                    $employee->name ?? null,
                    $employee->patronym ?? null,
                ])
            )
        );
    }
}
