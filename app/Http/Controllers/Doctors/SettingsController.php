<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Requests\ClinicSettingsUpdateRequest;
use App\Models\Clinic;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\Nurse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingsController
{
    private const NURSE_POSITIONS = [
        'Старшая медицинская сестра',
        'Медицинская сестра',
        'Процедурная медицинская сестра',
    ];

    private const DOCTOR_POSITIONS = [
        'Главный врач',
        'Врач',
        'Детский психиатр',
    ];

    private const MANAGED_STAFF_POSITIONS = [
        'Главный врач',
        'Врач',
        'Детский психиатр',
        'Старшая медицинская сестра',
        'Медицинская сестра',
        'Процедурная медицинская сестра',
        'Санитар',
        'Медицинский психолог',
        'Медицинский регистратор',
    ];

    private const SPECIALTY_MAP = [
        'Психиатрия' => 'Психиатр',
        'Детская психиатрия' => 'Детский психиатр',
        'Психотерапия' => 'Психотерапевт',
        'Клиническая психология' => 'Клинический психолог',
        'Сестринское дело' => 'Сестринское дело',
    ];

    private const SENIOR_PROFILE_SPECIALTIES = [
        'Психиатрия',
        'Детская психиатрия',
        'Психотерапия',
        'Клиническая психология',
    ];

    private const NURSING_PROFILE_SPECIALTIES = [
        'Сестринское дело',
    ];

    public function index()
    {
        $doctor = auth()->user()->doctor;
        $isPrivatePractice = !$doctor?->clinic_id;

        if ($isPrivatePractice) {
            return view(
                'doctors.settings.general',
                [
                    'doctor' => $doctor,
                    'clinic' => null,
                    'isPrivatePractice' => true,
                    'doctors' => collect(),
                    'clinicDoctors' => collect(),
                    'clinicNurses' => collect(),
                    'availableDoctors' => collect(),
                    'availableNurses' => collect(),
                    'staffSummary' => [
                        'doctors' => 0,
                        'nurses' => 0,
                    ],
                    'clinicPhones' => [],
                    'clinicServices' => [
                        'in_person',
                        'telemedicine',
                    ],
                    'weekDays' => [],
                ]
            );
        }

        $clinic = Clinic::findOrFail($doctor->clinic_id);

        /*
         * Принадлежность сотрудника клинике определяем только по clinic_id.
         * Старые doctor-записи с произвольным profession (например
         * "психиатр") не считаем оформленными сотрудниками настроек.
         */
        $clinicDoctorRows = Doctor::query()
            ->where('clinic_id', $clinic->id)
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        /*
         * Медсестёр показываем из nurses, а не через совпадение user_id.
         * nurses.id является идентификатором записи медсестры.
         */
        $clinicNurses = Nurse::query()
            ->where('clinic_id', $clinic->id)
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        /*
         * В списке сотрудников показываем ВСЕ Doctor-записи текущей
         * клиники, кроме явно сестринских/младших должностей.
         * Старые записи с profession = "психиатр" тоже являются врачами
         * клиники и не должны пропадать из интерфейса.
         */
        $doctors = $clinicDoctorRows
            ->reject(
                static function (Doctor $employee): bool {
                    $position = mb_strtolower(
                        trim((string) $employee->profession)
                    );

                    return in_array(
                        $position,
                        [
                            'старшая медицинская сестра',
                            'медицинская сестра',
                            'процедурная медицинская сестра',
                            'санитар',
                            'медицинский регистратор',
                        ],
                        true
                    );
                }
            )
            ->values();

        /*
         * Неврология удалена из доступных профилей. Старые значения
         * в doctors.status не показываем в интерфейсе даже до сохранения.
         */
        $homeVisitDistrictNames = collect();

        if (
            Schema::hasTable('clinic_districts')
            && Schema::hasColumn('clinic_districts', 'clinic_id')
            && Schema::hasColumn('clinic_districts', 'name')
            && Schema::hasColumn('clinic_districts', 'home_visit_doctor_id')
        ) {
            $homeVisitDistrictNames = DB::table('clinic_districts')
                ->where('clinic_id', $clinic->id)
                ->whereNotNull('home_visit_doctor_id')
                ->pluck('name', 'home_visit_doctor_id');
        }

        $doctors->each(
            function (Doctor $employee) use ($homeVisitDistrictNames): void {
                /*
                 * Старое profession = "психиатр" отображаем как обычную
                 * врачебную должность. В БД значение изменится только если
                 * пользователь действительно сохранит карточку сотрудника.
                 */
                if (
                    mb_strtolower(trim((string) $employee->profession))
                    === 'психиатр'
                ) {
                    $employee->profession = 'Врач';
                }

                $status = $this->specialtiesToStatus([
                    (string) ($employee->status ?? ''),
                ]);

                $employee->status = $status !== ''
                    ? $status
                    : null;

                $employee->setAttribute(
                    'home_visit_district_name',
                    $homeVisitDistrictNames->get($employee->id)
                );
            }
        );

        /*
         * Один Doctor.id = один врач. Количество его специальностей
         * на состав и счётчик врачей не влияет.
         */
        $clinicDoctors = $doctors->values();

        $staffSummary = [
            /*
             * Один Doctor.id = один врач. Количество специальностей
             * в doctors.status на счётчик не влияет.
             */
            'doctors' => $clinicDoctors->unique('id')->count(),
            'nurses' => $clinicNurses->unique('id')->count(),
        ];

        /*
         * Доступные для добавления записи doctors: только те, кто ещё
         * не привязан к клинике. Doctor-дубли медсестёр здесь не показываем.
         */
        $availableDoctors = Doctor::query()
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('clinic_id')
                        ->orWhere('clinic_id', 0);
                }
            )
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('profession')
                        ->orWhereNotIn(
                            'profession',
                            self::NURSE_POSITIONS
                        );
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $availableDoctors->each(
            function (Doctor $employee): void {
                $status = $this->specialtiesToStatus([
                    (string) ($employee->status ?? ''),
                ]);

                $employee->status = $status !== ''
                    ? $status
                    : null;
            }
        );

        /*
         * Доступные медсестры определяются только nurses.clinic_id.
         * user_id здесь специально не участвует.
         */
        $availableNurses = Nurse::query()
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('clinic_id')
                        ->orWhere('clinic_id', 0);
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $clinicPhones = old(
            'phones',
            $clinic->phones ?? []
        );

        if (is_string($clinicPhones)) {
            $clinicPhones = json_decode(
                $clinicPhones,
                true
            ) ?: [];
        }

        if (empty($clinicPhones)) {
            $clinicPhones = [
                [
                    'phone' => '',
                    'label' => 'Основная регистратура',
                ],
            ];
        }

        $clinicServices = old(
            'services',
            $clinic->services ?? [
                'in_person',
                'telemedicine',
            ]
        );

        if (is_string($clinicServices)) {
            $clinicServices = json_decode(
                $clinicServices,
                true
            ) ?: [];
        }

        $schedule = $clinic
            ->workSchedules()
            ->get()
            ->keyBy('day_number');

        $days = [
            1 => ['code' => 'mon', 'label' => 'ПН'],
            2 => ['code' => 'tue', 'label' => 'ВТ'],
            3 => ['code' => 'wed', 'label' => 'СР'],
            4 => ['code' => 'thu', 'label' => 'ЧТ'],
            5 => ['code' => 'fri', 'label' => 'ПТ'],
            6 => ['code' => 'sat', 'label' => 'СБ'],
            7 => ['code' => 'sun', 'label' => 'ВС'],
        ];

        $weekDays = [];

        foreach ($days as $dayNumber => $day) {
            $item = $schedule->get($dayNumber);

            $weekDays[] = [
                'code' => $day['code'],
                'label' => $day['label'],
                'open' => $item
                    ? (bool) $item->enabled
                    : false,
                'from' => $item && $item->start_time
                    ? substr((string) $item->start_time, 0, 5)
                    : '08:00',
                'to' => $item && $item->end_time
                    ? substr((string) $item->end_time, 0, 5)
                    : '20:00',
            ];
        }

        return view(
            'doctors.settings.general',
            compact(
                'doctor',
                'clinic',
                'isPrivatePractice',
                'doctors',
                'clinicDoctors',
                'clinicNurses',
                'availableDoctors',
                'availableNurses',
                'staffSummary',
                'clinicPhones',
                'clinicServices',
                'weekDays'
            )
        );
    }

    public function update(
        ClinicSettingsUpdateRequest $request
    ): RedirectResponse {
        $clinic = Clinic::findOrFail(
            auth()->user()->doctor->clinic_id
        );

        foreach ($request->array('employees') as $index => $employeeData) {
            if (
                $this->hasConflictingSpecialtyGroups(
                    $employeeData['specialties'] ?? []
                )
            ) {
                return back()
                    ->withErrors([
                        'employees.' . $index . '.specialties' =>
                            'Специальности врачебного профиля и сестринского дела нельзя выбирать одновременно.',
                    ])
                    ->withInput();
            }
        }

        DB::transaction(
            function () use ($request, $clinic): void {
                $clinic->name = $request->name;
                $clinic->address = $request->address;
                $clinic->description = $request->description;
                $clinic->phones = $request->phones;
                $clinic->city = $request->city;
                $clinic->services = $request->services;
                $clinic->save();

                foreach (
                    $request->array('working_hours')
                    as $key => $value
                ) {
                    ClinicWorkShedule::updateOrCreate(
                        [
                            'clinic_id' => $clinic->id,
                            'day_number' =>
                                ClinicWorkShedule::dayToNumber(
                                    $key
                                ),
                        ],
                        [
                            'enabled' =>
                                (bool) ($value['enabled'] ?? false),
                            'start_time' =>
                                $value['from'] ?? null,
                            'end_time' =>
                                $value['to'] ?? null,
                        ]
                    );
                }

                /*
                 * Сохраняем не только clinic_id.
                 * Раньше именно здесь терялись должность
                 * и выбранные специальности.
                 */
                foreach (
                    $request->array('employees')
                    as $value
                ) {
                    if (empty($value['id'])) {
                        continue;
                    }

                    /*
                     * Нельзя через форму текущей клиники забрать
                     * сотрудника, который уже относится к другой клинике.
                     * Разрешаем сотрудников текущей клиники, а также
                     * непривязанных сотрудников (NULL / 0).
                     */
                    $employee = Doctor::query()
                        ->where(
                            'id',
                            (int) $value['id']
                        )
                        ->where(
                            function ($query) use ($clinic): void {
                                $query
                                    ->where(
                                        'clinic_id',
                                        $clinic->id
                                    )
                                    ->orWhereNull('clinic_id')
                                    ->orWhere('clinic_id', 0);
                            }
                        )
                        ->first();

                    if (!$employee) {
                        continue;
                    }

                    $employee->clinic_id = $clinic->id;

                    if (!empty($value['position'])) {
                        $employee->profession =
                            trim(
                                (string) $value['position']
                            );
                    }

                    $status = $this->specialtiesToStatus(
                        $value['specialties'] ?? []
                    );

                    $employee->status = $status !== ''
                        ? $status
                        : null;

                    $this->fillOptionalEmployeeFields(
                        $employee,
                        $value
                    );

                    $employee->save();
                }
            }
        );

        Cache::forget(
            'clinic_' . $clinic->id
        );

        return redirect()
            ->route('doctors.settings.index')
            ->with(
                'success',
                'Параметры клиники были успешно сохранены.'
            );
    }

    /**
     * Обработчик формы route('doctors.settings.employees.update').
     *
     * Если маршрут у тебя уже смотрит на updateEmployee(), просто
     * замени SettingsController этим файлом.
     */
    public function updateEmployee(
        Request $request
    ): RedirectResponse {
        $user = auth()->user();
        $currentDoctor = $user?->doctor;

        abort_unless(
            $currentDoctor
                && (int) $currentDoctor->clinic_id > 0,
            403
        );

        $clinicId = (int) $currentDoctor->clinic_id;
        $clinic = Clinic::findOrFail($clinicId);

        $isAdmin = method_exists($user, 'hasRole')
            && $user->hasRole('admins');

        abort_unless(
            $isAdmin
                || (int) $clinic->headmaster_id
                    === (int) $currentDoctor->id,
            403
        );

        $employeeAction = (string) $request->input(
            'employee_action',
            'update'
        );

        $employeeSource = (string) $request->input(
            'employee_source',
            'doctor'
        );

        $isNurseSource = $employeeSource === 'nurse';

        $validator = Validator::make(
            $request->all(),
            [
                'employee_action' => [
                    'nullable',
                    Rule::in([
                        'add',
                        'update',
                    ]),
                ],

                'employee_source' => [
                    'nullable',
                    Rule::in([
                        'doctor',
                        'nurse',
                    ]),
                ],

                'doctor_id' => [
                    Rule::requiredIf(!$isNurseSource),
                    'nullable',
                    'integer',
                    Rule::exists('doctors', 'id'),
                ],

                'nurse_id' => [
                    Rule::requiredIf($isNurseSource),
                    'nullable',
                    'integer',
                    Rule::exists('nurses', 'id'),
                ],

                'position' => [
                    'required',
                    'string',
                    Rule::in(self::MANAGED_STAFF_POSITIONS),
                ],

                'employment_status' => [
                    'required',
                    Rule::in([
                        'active',
                        'temporarily_absent',
                        'dismissed',
                    ]),
                ],

                'dismissed_at' => [
                    'nullable',
                    'date',
                ],

                'specialties' => [
                    'nullable',
                    'array',
                    'max:20',
                ],

                'specialties.*' => [
                    'string',
                    'distinct',
                    Rule::in(
                        array_keys(self::SPECIALTY_MAP)
                    ),
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:32',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'new_headmaster_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('doctors', 'id')
                        ->where(
                            static function ($query) use ($clinicId): void {
                                $query
                                    ->where('clinic_id', $clinicId)
                                    ->whereIn(
                                        'profession',
                                        self::DOCTOR_POSITIONS
                                    );
                            }
                        ),
                ],
            ],
            [
                'doctor_id.required' =>
                    'Не удалось определить сотрудника.',
                'doctor_id.exists' =>
                    'Сотрудник не найден.',
                'nurse_id.required' =>
                    'Не удалось определить медицинскую сестру.',
                'nurse_id.exists' =>
                    'Медицинская сестра не найдена.',
                'specialties.*.in' =>
                    'Выбрана неизвестная специальность.',
                'new_headmaster_id.exists' =>
                    'Новый главный врач должен работать в этой клинике.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors(
                    $validator,
                    'employee'
                )
                ->withInput();
        }

        $data = $validator->validated();

        if (
            $this->hasConflictingSpecialtyGroups(
                $data['specialties'] ?? []
            )
        ) {
            return back()
                ->withErrors(
                    [
                        'specialties' =>
                            'Специальности врачебного профиля и сестринского дела нельзя выбирать одновременно.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        /*
         * Медсестра живёт в nurses. user_id здесь не используется
         * ни для поиска, ни для дедупликации, ни для изменения clinic_id.
         */
        if ($isNurseSource) {
            if (
                !in_array(
                    $data['position'],
                    self::NURSE_POSITIONS,
                    true
                )
            ) {
                return back()
                    ->withErrors(
                        [
                            'position' =>
                                'Для медицинской сестры выберите сестринскую должность.',
                        ],
                        'employee'
                    )
                    ->withInput();
            }

            $nurseQuery = Nurse::query()
                ->where(
                    'id',
                    (int) $data['nurse_id']
                );

            if ($employeeAction === 'add') {
                $nurseQuery->where(
                    static function ($query) use ($clinicId): void {
                        $query
                            ->where('clinic_id', $clinicId)
                            ->orWhereNull('clinic_id')
                            ->orWhere('clinic_id', 0);
                    }
                );
            } else {
                $nurseQuery->where('clinic_id', $clinicId);
            }

            $nurse = $nurseQuery->first();

            if (!$nurse) {
                return back()
                    ->withErrors(
                        [
                            'nurse_id' =>
                                $employeeAction === 'add'
                                    ? 'Медицинская сестра уже относится к другой клинике или недоступна для добавления.'
                                    : 'Медицинская сестра не относится к текущей клинике.',
                        ],
                        'employee'
                    )
                    ->withInput();
            }

            DB::transaction(
                function () use (
                    $nurse,
                    $data,
                    $clinicId
                ): void {
                    $nurse->clinic_id =
                        $data['employment_status'] === 'dismissed'
                            ? 0
                            : $clinicId;

                    $nurse->profession = $data['position'];

                    if (Schema::hasColumn('nurses', 'phone')) {
                        $nurse->phone = $data['phone'] ?? null;
                    }

                    if (Schema::hasColumn('nurses', 'email')) {
                        $nurse->email = $data['email'] ?? null;
                    }

                    if (Schema::hasColumn('nurses', 'employment_status')) {
                        $nurse->employment_status =
                            $data['employment_status'];
                    }

                    if (Schema::hasColumn('nurses', 'dismissed_at')) {
                        $nurse->dismissed_at =
                            $data['employment_status'] === 'dismissed'
                                ? ($data['dismissed_at'] ?? now())
                                : null;
                    }

                    $nurse->save();
                }
            );

            Cache::forget('clinic_' . $clinicId);

            return redirect()
                ->route('doctors.settings.index')
                ->with(
                    'employee_success',
                    $employeeAction === 'add'
                        ? 'Медицинская сестра была успешно добавлена в клинику.'
                        : 'Данные медицинской сестры были успешно сохранены.'
                );
        }

        if (
            in_array(
                $data['position'],
                self::NURSE_POSITIONS,
                true
            )
        ) {
            return back()
                ->withErrors(
                    [
                        'position' =>
                            'Сестринский персонал должен редактироваться через запись из таблицы nurses.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        $employeeQuery = Doctor::query()
            ->where(
                'id',
                (int) $data['doctor_id']
            );

        if ($employeeAction === 'add') {
            $employeeQuery->where(
                static function ($query) use ($clinicId): void {
                    $query
                        ->where('clinic_id', $clinicId)
                        ->orWhereNull('clinic_id')
                        ->orWhere('clinic_id', 0);
                }
            );
        } else {
            $employeeQuery->where('clinic_id', $clinicId);
        }

        $employee = $employeeQuery->first();

        if (!$employee) {
            return back()
                ->withErrors(
                    [
                        'doctor_id' =>
                            $employeeAction === 'add'
                                ? 'Сотрудник уже относится к другой клинике или недоступен для добавления.'
                                : 'Сотрудник не относится к текущей клинике.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        $isCurrentHeadmaster =
            $employeeAction !== 'add'
            && (int) $clinic->headmaster_id
                === (int) $employee->id;

        $headmasterTransferRequired =
            $isCurrentHeadmaster
            && (
                $data['employment_status'] === 'dismissed'
                || $data['position'] !== 'Главный врач'
            );

        if (
            $headmasterTransferRequired
            && empty($data['new_headmaster_id'])
        ) {
            return back()
                ->withErrors(
                    [
                        'new_headmaster_id' =>
                            'Выберите нового главного врача.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        if (
            !empty($data['new_headmaster_id'])
            && (int) $data['new_headmaster_id']
                === (int) $employee->id
        ) {
            return back()
                ->withErrors(
                    [
                        'new_headmaster_id' =>
                            'Сотрудник не может передать должность самому себе.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        DB::transaction(
            function () use (
                $data,
                $employee,
                $clinic,
                $clinicId,
                $employeeAction,
                $headmasterTransferRequired
            ): void {
                if ($employeeAction === 'add') {
                    $employee->clinic_id = $clinicId;
                }

                $employee->profession = $data['position'];

                $status = $this->specialtiesToStatus(
                    $data['specialties'] ?? []
                );

                $employee->status = $status !== ''
                    ? $status
                    : null;

                $this->fillOptionalEmployeeFields(
                    $employee,
                    $data
                );

                if (Schema::hasColumn('doctors', 'employment_status')) {
                    $employee->employment_status =
                        $data['employment_status'];
                }

                if (Schema::hasColumn('doctors', 'dismissed_at')) {
                    $employee->dismissed_at =
                        $data['employment_status'] === 'dismissed'
                            ? ($data['dismissed_at'] ?? now())
                            : null;
                }

                if ($data['employment_status'] === 'dismissed') {
                    $employee->clinic_id = 0;
                }

                $employee->save();

                if ($headmasterTransferRequired) {
                    $clinic->headmaster_id =
                        (int) $data['new_headmaster_id'];
                    $clinic->save();
                } elseif (
                    $employeeAction !== 'add'
                    && $data['position'] === 'Главный врач'
                    && $data['employment_status'] !== 'dismissed'
                ) {
                    $clinic->headmaster_id = $employee->id;
                    $clinic->save();
                }
            }
        );

        Cache::forget('clinic_' . $clinicId);

        return redirect()
            ->route('doctors.settings.index')
            ->with(
                'employee_success',
                $employeeAction === 'add'
                    ? 'Сотрудник был успешно добавлен в клинику.'
                    : 'Данные сотрудника были успешно сохранены.'
            );
    }

    private function hasConflictingSpecialtyGroups(
        array $specialties
    ): bool {
        $specialties = collect($specialties)
            ->flatMap(
                static fn ($value): array =>
                    array_map(
                        'trim',
                        preg_split('/[,;|]/u', (string) $value) ?: []
                    )
            )
            ->filter()
            ->map(
                fn (string $value): string =>
                    $this->normalizeSpecialtyForGroup($value)
            )
            ->filter()
            ->unique();

        return $specialties
                ->intersect(self::SENIOR_PROFILE_SPECIALTIES)
                ->isNotEmpty()
            && $specialties
                ->intersect(self::NURSING_PROFILE_SPECIALTIES)
                ->isNotEmpty();
    }

    private function normalizeSpecialtyForGroup(
        string $value
    ): string {
        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            'психиатр', 'психиатрия' => 'Психиатрия',
            'детский психиатр',
            'врач-психиатр детский',
            'детская психиатрия' => 'Детская психиатрия',
            'психотерапевт', 'психотерапия' => 'Психотерапия',
            'медицинский психолог',
            'клинический психолог',
            'клиническая психология' => 'Клиническая психология',
            'медицинская сестра',
            'старшая медицинская сестра',
            'процедурная медицинская сестра',
            'сестринское дело' => 'Сестринское дело',
            default => trim($value),
        };
    }

    private function isRemovedSpecialty(
        string $value
    ): bool {
        return in_array(
            mb_strtolower(trim($value)),
            [
                'невролог',
                'неврология',
            ],
            true
        );
    }

    private function specialtiesToStatus(
        array $specialties
    ): string {
        return collect($specialties)
            ->flatMap(
                static function ($value): array {
                    return array_map(
                        'trim',
                        preg_split('/[,;|]/u', (string) $value) ?: []
                    );
                }
            )
            ->filter()
            ->reject(
                fn (string $specialty): bool =>
                    $this->isRemovedSpecialty($specialty)
            )
            ->map(
                static function (string $specialty): string {
                    return self::SPECIALTY_MAP[$specialty]
                        ?? $specialty;
                }
            )
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    }

    private function fillOptionalEmployeeFields(
        Doctor $employee,
        array $data
    ): void {
        if (
            Schema::hasColumn('doctors', 'phone')
        ) {
            $employee->phone =
                $data['phone'] ?? null;
        }

        if (
            Schema::hasColumn('doctors', 'email')
        ) {
            $employee->email =
                $data['email'] ?? null;
        }
    }
}
