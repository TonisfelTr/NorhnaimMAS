<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicOfficeStoreRequest;
use App\Models\ClinicBuilding;
use App\Models\ClinicCabinet;
use App\Models\ClinicCabinetSchedule;
use App\Models\ClinicDistrict;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\EmployeeWorkSchedule;
use App\Models\Nurse;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CabinetController extends Controller
{
    private function saveOffice(ClinicOfficeStoreRequest $request)
    {
        $clinic_id = auth()->user()->doctor->clinic_id;

        $homeVisitDoctorIds = $this->homeVisitDoctorIdsForClinic(
            (int) $clinic_id
        );

        foreach ([
            $request->integer('doctor'),
            $request->integer('replace_doctor_id'),
        ] as $doctorId) {
            if (
                $doctorId > 0
                && in_array($doctorId, $homeVisitDoctorIds, true)
            ) {
                throw ValidationException::withMessages([
                    'doctor' => 'Этот психиатр закреплён за выездами на дом и не может быть назначен в кабинет или на замещение.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $clinic_id): void {
            if ($request->mode === 'edit') {
                $cabinet = ClinicCabinet::where(
                    'id',
                    $request->office_id
                )
                    ->where('clinic_id', $clinic_id)
                    ->firstOrFail();
            } else {
                $cabinet = new ClinicCabinet();
                $cabinet->clinic_id = $clinic_id;
            }

            $cabinet->doctor_id = in_array(
                $request->cabinet_type,
                [
                    ClinicCabinet::TYPE_DOCTOR,
                    ClinicCabinet::TYPE_FUNCTIONAL,
                ],
                true
            )
                ? $request->doctor
                : null;

            $cabinet->cabinet_type = $request->cabinet_type;
            /*
             * Медсестра может быть назначена:
             * - во врачебный кабинет — опционально;
             * - в процедурный — обязательно (проверяет Request);
             * - в КФД — опционально.
             */
            $cabinet->nurse_id = $request->nurse;
            $cabinet->building_id =
                $request->integer('building_id');
            $cabinet->cabinet = $request->number;
            $cabinet->stage = $request->stage;
            $cabinet->name = $request->name;
            $cabinet->functional_methods =
                $request->cabinet_type === ClinicCabinet::TYPE_FUNCTIONAL
                && $request->filled('functional_method')
                    ? [
                    $request
                        ->string('functional_method')
                        ->toString(),
                ]
                    : [];

            $cabinet->count_107 =
                $request->cabinet_type === ClinicCabinet::TYPE_DOCTOR
                    ? $request->recipe_107
                    : 0;

            $cabinet->count_148 =
                $request->cabinet_type === ClinicCabinet::TYPE_DOCTOR
                    ? $request->recipe_148
                    : 0;

            $cabinet->phone = $request->phone;
            $cabinet->enabled = $request->status === 'active';

            if ($request->mode === 'edit') {
                $cabinet->replace_doctor_id =
                    $request->cabinet_type
                    !== ClinicCabinet::TYPE_PROCEDURE
                        ? $request->replace_doctor_id
                        : null;

                $cabinet->replace_nurse_id =
                    $cabinet->nurse_id
                        ? $request->replace_nurse_id
                        : null;
            } else {
                $cabinet->replace_doctor_id = null;
                $cabinet->replace_nurse_id = null;
            }

            $cabinet->save();

            /*
             * Убираем только точное встречное замещение.
             *
             * Пример ошибочного состояния:
             * КФД: основной Fay, замещает Hudson;
             * кабинет Hudson: основной Hudson, замещает Fay.
             *
             * В таком случае интерфейс начинал показывать перенос
             * в обе стороны. Собственный врачебный кабинет
             * замещающего не должен одновременно содержать
             * обратную ссылку на врача, которого он замещает.
             */
            if (
                $cabinet->doctor_id
                && $cabinet->replace_doctor_id
            ) {
                ClinicCabinet::where(
                    'clinic_id',
                    $clinic_id
                )
                    ->where(
                        'cabinet_type',
                        ClinicCabinet::TYPE_DOCTOR
                    )
                    ->where(
                        'doctor_id',
                        $cabinet->replace_doctor_id
                    )
                    ->where(
                        'replace_doctor_id',
                        $cabinet->doctor_id
                    )
                    ->where(
                        'id',
                        '<>',
                        $cabinet->id
                    )
                    ->update([
                        'replace_doctor_id' => null,
                    ]);
            }

            if (
                $cabinet->cabinet_type
                !== ClinicCabinet::TYPE_DOCTOR
            ) {
                Patient::where('clinic_id', $clinic_id)
                    ->where('cabinet_id', $cabinet->id)
                    ->update([
                        'cabinet_id' => null,
                    ]);
            }

            if (
                in_array(
                    $request->cabinet_type,
                    [
                        ClinicCabinet::TYPE_DOCTOR,
                        ClinicCabinet::TYPE_FUNCTIONAL,
                    ],
                    true
                )
                && $cabinet->doctor_id
            ) {
                $doctorSchedule = $request->input('doctor_schedule', []);

                $hasWorkingDay = collect($doctorSchedule)
                    ->contains(
                        static function ($hours): bool {
                            return !empty($hours['enabled']);
                        }
                    );

                if (!$hasWorkingDay) {
                    EmployeeWorkSchedule::where('clinic_id', $clinic_id)
                        ->where('employee_type', 'doctor')
                        ->where('employee_id', $cabinet->doctor_id)
                        ->delete();
                } else {
                    $dayNumbers = [];

                    foreach ($doctorSchedule as $code => $hours) {
                        $dayNumber = EmployeeWorkSchedule::dayToNumber($code);
                        $dayNumbers[] = $dayNumber;

                        EmployeeWorkSchedule::updateOrCreate(
                            [
                                'clinic_id' => $clinic_id,
                                'employee_type' => 'doctor',
                                'employee_id' => $cabinet->doctor_id,
                                'day_number' => $dayNumber,
                            ],
                            [
                                'enabled' => (bool) ($hours['enabled'] ?? false),
                                'start_time' => !empty($hours['enabled'])
                                    ? ($hours['from'] ?? null)
                                    : null,
                                'end_time' => !empty($hours['enabled'])
                                    ? ($hours['to'] ?? null)
                                    : null,
                            ]
                        );
                    }

                    EmployeeWorkSchedule::where('clinic_id', $clinic_id)
                        ->where('employee_type', 'doctor')
                        ->where('employee_id', $cabinet->doctor_id)
                        ->whereNotIn('day_number', $dayNumbers)
                        ->delete();
                }
            }

            if ($request->cabinet_type === ClinicCabinet::TYPE_PROCEDURE) {
                $dayNumbers = [];

                foreach (
                    $request->input('procedure_schedule', [])
                    as $code => $hours
                ) {
                    $dayNumber = ClinicCabinetSchedule::dayToNumber($code);
                    $dayNumbers[] = $dayNumber;

                    ClinicCabinetSchedule::updateOrCreate(
                        [
                            'clinic_cabinet_id' => $cabinet->id,
                            'day_number' => $dayNumber,
                        ],
                        [
                            'enabled' => (bool) (
                                $hours['enabled'] ?? false
                            ),
                            'start_time' => !empty($hours['enabled'])
                                ? ($hours['from'] ?? null)
                                : null,
                            'end_time' => !empty($hours['enabled'])
                                ? ($hours['to'] ?? null)
                                : null,
                        ]
                    );
                }

                if ($dayNumbers) {
                    $cabinet->procedureSchedules()
                        ->whereNotIn('day_number', $dayNumbers)
                        ->delete();
                } else {
                    $cabinet->procedureSchedules()->delete();
                }
            } else {
                $cabinet->procedureSchedules()->delete();
            }
        });

        return back()->with(
            'success',
            $request->mode === 'edit'
                ? 'Кабинет был успешно обновлён.'
                : 'Кабинет был успешно создан.'
        );
    }

    public function index()
    {
        $clinic_id = auth()->user()->doctor->clinic_id;

        $clinicSchedule = ClinicWorkShedule::where('clinic_id', $clinic_id)
            ->where('enabled', true)
            ->get()
            ->keyBy('day_number');

        $procedureWeekDays = collect(ClinicCabinetSchedule::DAYS)
            ->filter(
                function (
                    array $day,
                    int $dayNumber
                ) use ($clinicSchedule): bool {
                    return $clinicSchedule->has($dayNumber);
                }
            )
            ->map(
                function (
                    array $day,
                    int $dayNumber
                ) use ($clinicSchedule): array {
                    $clinicDay = $clinicSchedule->get($dayNumber);

                    return [
                        'code' => $day['code'],
                        'label' => $day['label'],
                        'open' => false,
                        'from' => substr(
                            (string) $clinicDay->start_time,
                            0,
                            5
                        ),
                        'to' => substr(
                            (string) $clinicDay->end_time,
                            0,
                            5
                        ),
                    ];
                }
            )
            ->values()
            ->all();

        $buildings = ClinicBuilding::where(
            'clinic_id',
            $clinic_id
        )
            ->where('enabled', true)
            ->withCount('cabinets')
            ->orderBy('number')
            ->orderBy('name')
            ->get();

        $selectedBuildingId = request()->filled('building_id')
            ? request()->integer('building_id')
            : null;

        $selectedBuilding = $selectedBuildingId
            ? $buildings->firstWhere(
                'id',
                $selectedBuildingId
            )
            : null;

        /*
         * Если в query string передали чужой/неактивный корпус,
         * не используем его в фильтре.
         */
        if ($selectedBuildingId && !$selectedBuilding) {
            $selectedBuildingId = null;
        }

        /*
         * Кабинеты сортируются сначала по корпусу,
         * а уже внутри корпуса — по номеру кабинета.
         *
         * Поэтому кабинет №15 корпуса №1 и кабинет №15 корпуса №2
         * остаются в разных группах и никогда не смешиваются.
         */
        $cabinets = ClinicCabinet::query()
            ->select('clinic_cabinets.*')
            ->leftJoin(
                'clinic_buildings',
                'clinic_buildings.id',
                '=',
                'clinic_cabinets.building_id'
            )
            ->with([
                'building',
                'mainDoctor',
                'mainNurse',
                'alternativeDoctor',
                'alternativeNurse',
                'procedureSchedules',
            ])
            ->withCount('patients')
            ->where(
                'clinic_cabinets.enabled',
                true
            )
            ->where(
                'clinic_cabinets.clinic_id',
                $clinic_id
            )
            ->when(
                $selectedBuildingId,
                static function (
                    $query,
                    int $buildingId
                ): void {
                    $query->where(
                        'clinic_cabinets.building_id',
                        $buildingId
                    );
                }
            )
            ->orderByRaw(
                "NULLIF(
                    substring(
                        clinic_buildings.number
                        from '^[0-9]+'
                    ),
                    ''
                )::integer ASC NULLS LAST"
            )
            ->orderBy('clinic_buildings.number')
            ->orderBy('clinic_buildings.name')
            ->orderByRaw(
                "NULLIF(
                    substring(
                        clinic_cabinets.cabinet
                        from '^[0-9]+'
                    ),
                    ''
                )::integer ASC NULLS LAST"
            )
            ->orderBy('clinic_cabinets.cabinet')
            ->paginate(10)
            ->withQueryString();

        $cabinets->getCollection()->each(
            function (ClinicCabinet $cabinet) use ($clinicSchedule): void {
                $schedule = $cabinet->procedureSchedules
                    ->keyBy('day_number');

                $cabinet->procedure_schedule = collect(
                    ClinicCabinetSchedule::DAYS
                )
                    ->filter(
                        function (
                            array $day,
                            int $dayNumber
                        ) use ($clinicSchedule): bool {
                            return $clinicSchedule->has($dayNumber);
                        }
                    )
                    ->map(
                        function (
                            array $day,
                            int $dayNumber
                        ) use ($schedule, $clinicSchedule): array {
                            $item = $schedule->get($dayNumber);
                            $clinicDay = $clinicSchedule->get($dayNumber);

                            return [
                                'code' => $day['code'],
                                'label' => $day['label'],
                                'open' => $item
                                    ? (bool) $item->enabled
                                    : false,

                                'from' => $item && $item->start_time
                                    ? substr(
                                        (string) $item->start_time,
                                        0,
                                        5
                                    )
                                    : substr(
                                        (string) $clinicDay->start_time,
                                        0,
                                        5
                                    ),

                                'to' => $item && $item->end_time
                                    ? substr(
                                        (string) $item->end_time,
                                        0,
                                        5
                                    )
                                    : substr(
                                        (string) $clinicDay->end_time,
                                        0,
                                        5
                                    ),
                            ];
                        }
                    )
                    ->values()
                    ->all();
            }
        );

        $doctorIds = $cabinets->getCollection()
            ->pluck('doctor_id')
            ->filter()
            ->unique()
            ->values();

        $nurseIds = $cabinets->getCollection()
            ->pluck('nurse_id')
            ->filter()
            ->unique()
            ->values();

        $employeeSchedules = collect();

        if ($doctorIds->isNotEmpty() || $nurseIds->isNotEmpty()) {
            $employeeSchedules = EmployeeWorkSchedule::where(
                'clinic_id',
                $clinic_id
            )
                ->where(
                    function ($query) use ($doctorIds, $nurseIds): void {
                        if ($doctorIds->isNotEmpty()) {
                            $query->where(
                                function ($query) use ($doctorIds): void {
                                    $query
                                        ->where('employee_type', 'doctor')
                                        ->whereIn('employee_id', $doctorIds);
                                }
                            );
                        }

                        if ($nurseIds->isNotEmpty()) {
                            $method = $doctorIds->isNotEmpty()
                                ? 'orWhere'
                                : 'where';

                            $query->{$method}(
                                function ($query) use ($nurseIds): void {
                                    $query
                                        ->where('employee_type', 'nurse')
                                        ->whereIn('employee_id', $nurseIds);
                                }
                            );
                        }
                    }
                )
                ->get()
                ->groupBy(
                    static function (
                        EmployeeWorkSchedule $item
                    ): string {
                        return $item->employee_type
                            . ':'
                            . $item->employee_id;
                    }
                );
        }

        $cabinets->getCollection()->each(
            function (ClinicCabinet $cabinet) use (
                $employeeSchedules,
                $clinicSchedule
            ): void {
                $employeeType = null;
                $employeeId = null;
                $source = null;

                if ($cabinet->doctor_id) {
                    $employeeType = 'doctor';
                    $employeeId = (int) $cabinet->doctor_id;
                    $source = 'По расписанию врача'
                        . (
                            $cabinet->mainDoctor
                                ? ' — ' . $cabinet->mainDoctor->full_name
                                : ''
                        );
                } elseif ($cabinet->nurse_id) {
                    $employeeType = 'nurse';
                    $employeeId = (int) $cabinet->nurse_id;
                    $source = 'По расписанию медсестры'
                        . (
                            $cabinet->mainNurse
                                ? ' — ' . $cabinet->mainNurse->full_name
                                : ''
                        );
                }

                if (!$employeeType || !$employeeId) {
                    $cabinet->work_schedule = [];
                    $cabinet->work_schedule_source = null;

                    return;
                }

                $scheduleItems = $employeeSchedules->get(
                    $employeeType . ':' . $employeeId,
                    collect()
                );

                /*
                 * Для уже созданных процедурных кабинетов сохраняем
                 * обратную совместимость: пока расписание медсестры
                 * не заполнено, показываем старое расписание кабинета.
                 */
                if (
                    $scheduleItems->isEmpty()
                    && !$cabinet->doctor_id
                    && $cabinet->procedureSchedules->isNotEmpty()
                ) {
                    $cabinet->work_schedule =
                        $cabinet->procedure_schedule ?? [];

                    $cabinet->work_schedule_source =
                        'По расписанию процедурного кабинета';

                    return;
                }

                if ($scheduleItems->isEmpty()) {
                    $cabinet->work_schedule = [];
                    $cabinet->work_schedule_source = $source;

                    return;
                }

                $schedule = $scheduleItems->keyBy('day_number');

                $cabinet->work_schedule = collect(
                    EmployeeWorkSchedule::DAYS
                )
                    ->filter(
                        static function (
                            array $day,
                            int $dayNumber
                        ) use ($clinicSchedule): bool {
                            return $clinicSchedule->has($dayNumber);
                        }
                    )
                    ->map(
                        static function (
                            array $day,
                            int $dayNumber
                        ) use ($schedule, $clinicSchedule): array {
                            $item = $schedule->get($dayNumber);
                            $clinicDay = $clinicSchedule->get($dayNumber);

                            return [
                                'code' => $day['code'],
                                'label' => $day['label'],
                                'open' => $item
                                    ? (bool) $item->enabled
                                    : false,
                                'from' => $item && $item->start_time
                                    ? substr(
                                        (string) $item->start_time,
                                        0,
                                        5
                                    )
                                    : substr(
                                        (string) $clinicDay->start_time,
                                        0,
                                        5
                                    ),
                                'to' => $item && $item->end_time
                                    ? substr(
                                        (string) $item->end_time,
                                        0,
                                        5
                                    )
                                    : substr(
                                        (string) $clinicDay->end_time,
                                        0,
                                        5
                                    ),
                            ];
                        }
                    )
                    ->values()
                    ->all();

                $cabinet->work_schedule_source = $source;
            }
        );

        /*
         * Выездной психиатр участка не должен появляться в выборе
         * врача кабинета и в списке замещающих врачей.
         */
        $homeVisitDoctorIds = $this->homeVisitDoctorIdsForClinic(
            (int) $clinic_id
        );

        $doctors = $this->clinicDoctorsQuery((int) $clinic_id)
            ->when(
                !empty($homeVisitDoctorIds),
                static fn ($query) =>
                    $query->whereNotIn('id', $homeVisitDoctorIds)
            )
            ->select([
                'id',
                'name',
                'surname',
                'patronym',
                'status',
                'profession',
            ])
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $doctors->each(function (Doctor $doctor): void {
            $doctor->status = $this->cleanDoctorSpeciality(
                (string) ($doctor->status ?? '')
            );
        });

        $doctorScheduleRows = EmployeeWorkSchedule::where(
            'clinic_id',
            $clinic_id
        )
            ->where('employee_type', 'doctor')
            ->whereIn('employee_id', $doctors->pluck('id'))
            ->get()
            ->groupBy('employee_id');

        $todayDayNumber = now()->isoWeekday();

        $todayDoctorSchedules = $doctorScheduleRows
            ->map(
                static function ($items) use ($todayDayNumber) {
                    return $items->first(
                        static function (
                            EmployeeWorkSchedule $item
                        ) use ($todayDayNumber): bool {
                            return (int) $item->day_number
                                    === (int) $todayDayNumber
                                && (bool) $item->enabled
                                && !empty($item->start_time)
                                && !empty($item->end_time);
                        }
                    );
                }
            )
            ->filter();

        $replacementAssignments = ClinicCabinet::where(
            'clinic_id',
            $clinic_id
        )
            ->where('enabled', true)
            ->whereIn(
                'cabinet_type',
                [
                    ClinicCabinet::TYPE_DOCTOR,
                    ClinicCabinet::TYPE_FUNCTIONAL,
                ]
            )
            ->whereNotNull('doctor_id')
            ->whereNotNull('replace_doctor_id')
            ->with([
                'mainDoctor:id,name,surname,patronym',
                'building:id,number,name',
            ])
            ->get([
                'id',
                'clinic_id',
                'building_id',
                'doctor_id',
                'replace_doctor_id',
                'cabinet',
                'name',
                'stage',
                'cabinet_type',
            ]);

        $replacementDoctorIds = $replacementAssignments
            ->pluck('replace_doctor_id')
            ->filter()
            ->unique()
            ->values();

        $replacementDoctorOwnCabinets = ClinicCabinet::where(
            'clinic_id',
            $clinic_id
        )
            ->where('enabled', true)
            ->where(
                'cabinet_type',
                ClinicCabinet::TYPE_DOCTOR
            )
            ->whereIn('doctor_id', $replacementDoctorIds)
            ->get([
                'id',
                'doctor_id',
                'replace_doctor_id',
                'cabinet',
                'name',
                'stage',
                'cabinet_type',
            ])
            ->keyBy('doctor_id');

        $pageCabinetsById = $cabinets
            ->getCollection()
            ->keyBy('id');

        $pageCabinetsById->each(
            static function (ClinicCabinet $cabinet): void {
                $cabinet->doctor_other_cabinets_today = [];
            }
        );

        $timeToMinutes = static function ($value): ?int {
            if (!$value) {
                return null;
            }

            $time = substr((string) $value, 0, 5);

            if (!preg_match('/^(\d{2}):(\d{2})$/', $time, $matches)) {
                return null;
            }

            return ((int) $matches[1] * 60)
                + (int) $matches[2];
        };

        $minutesToTime = static function (int $minutes): string {
            return sprintf(
                '%02d:%02d',
                intdiv($minutes, 60),
                $minutes % 60
            );
        };

        foreach ($replacementAssignments as $assignment) {
            /*
             * Время замещения на сегодня берём из расписания
             * ОСНОВНОГО врача кабинета, которого замещают.
             *
             * Это важнее собственного графика замещающего врача:
             * если ему назначено замещение с 08:00 до 14:00,
             * на карточке его собственного кабинета нужно показать,
             * что сегодня в этот период он принимает в другом кабинете,
             * даже если его обычный приём в своём кабинете начинается
             * только с 14:00.
             */
            $mainDoctorSchedule = $todayDoctorSchedules->get(
                $assignment->doctor_id
            );

            if (!$mainDoctorSchedule) {
                continue;
            }

            $replacementFrom = $timeToMinutes(
                $mainDoctorSchedule->start_time
            );

            $replacementTo = $timeToMinutes(
                $mainDoctorSchedule->end_time
            );

            if (
                $replacementFrom === null
                || $replacementTo === null
                || $replacementFrom >= $replacementTo
            ) {
                continue;
            }

            $replacementDoctorSchedule = $todayDoctorSchedules->get(
                $assignment->replace_doctor_id
            );

            $hasOwnScheduleOverlap = false;
            $overlapFrom = null;
            $overlapTo = null;

            if ($replacementDoctorSchedule) {
                $ownFrom = $timeToMinutes(
                    $replacementDoctorSchedule->start_time
                );

                $ownTo = $timeToMinutes(
                    $replacementDoctorSchedule->end_time
                );

                if (
                    $ownFrom !== null
                    && $ownTo !== null
                    && $ownFrom < $ownTo
                ) {
                    $candidateFrom = max(
                        $replacementFrom,
                        $ownFrom
                    );

                    $candidateTo = min(
                        $replacementTo,
                        $ownTo
                    );

                    if ($candidateFrom < $candidateTo) {
                        $hasOwnScheduleOverlap = true;
                        $overlapFrom = $candidateFrom;
                        $overlapTo = $candidateTo;
                    }
                }
            }

            $ownCabinet = $replacementDoctorOwnCabinets->get(
                $assignment->replace_doctor_id
            );

            if (!$ownCabinet) {
                continue;
            }

            /*
             * Плашку ставим именно на собственный кабинет
             * замещающего врача.
             */
            $pageCabinet = $pageCabinetsById->get(
                $ownCabinet->id
            );

            if (
                !$pageCabinet
                || $pageCabinet->cabinet_type
                    !== ClinicCabinet::TYPE_DOCTOR
                || (int) $pageCabinet->id
                    === (int) $assignment->id
            ) {
                continue;
            }

            $locations = $pageCabinet
                ->doctor_other_cabinets_today
                ?? [];

            $locations[] = [
                'cabinet_id' => $assignment->id,
                'cabinet' => $assignment->cabinet,
                'name' => $assignment->name,
                'stage' => $assignment->stage,
                'building_number' =>
                    $assignment->building?->number,
                'building' =>
                    $assignment->building?->name,

                /*
                 * Показываем полный период замещения,
                 * а не только математическое пересечение
                 * с собственным графиком.
                 */
                'from' => $minutesToTime(
                    $replacementFrom
                ),
                'to' => $minutesToTime(
                    $replacementTo
                ),

                'has_own_schedule_overlap' =>
                    $hasOwnScheduleOverlap,

                'overlap_from' =>
                    $overlapFrom !== null
                        ? $minutesToTime($overlapFrom)
                        : null,

                'overlap_to' =>
                    $overlapTo !== null
                        ? $minutesToTime($overlapTo)
                        : null,

                'replaced_doctor' =>
                    $assignment->mainDoctor?->full_name,
            ];

            $pageCabinet->doctor_other_cabinets_today =
                $locations;
        }

        $doctorScheduleMap = $doctors
            ->mapWithKeys(
                function (Doctor $doctor) use (
                    $doctorScheduleRows,
                    $clinicSchedule
                ): array {
                    $schedule = $doctorScheduleRows
                        ->get($doctor->id, collect())
                        ->keyBy('day_number');

                    $items = collect(EmployeeWorkSchedule::DAYS)
                        ->filter(
                            static function (
                                array $day,
                                int $dayNumber
                            ) use ($clinicSchedule): bool {
                                return $clinicSchedule->has($dayNumber);
                            }
                        )
                        ->map(
                            static function (
                                array $day,
                                int $dayNumber
                            ) use ($schedule, $clinicSchedule): array {
                                $item = $schedule->get($dayNumber);
                                $clinicDay = $clinicSchedule->get($dayNumber);

                                return [
                                    'code' => $day['code'],
                                    'label' => $day['label'],
                                    'open' => $item
                                        ? (bool) $item->enabled
                                        : false,
                                    'from' => $item && $item->start_time
                                        ? substr((string) $item->start_time, 0, 5)
                                        : substr((string) $clinicDay->start_time, 0, 5),
                                    'to' => $item && $item->end_time
                                        ? substr((string) $item->end_time, 0, 5)
                                        : substr((string) $clinicDay->end_time, 0, 5),
                                ];
                            }
                        )
                        ->values()
                        ->all();

                    return [$doctor->id => $items];
                }
            )
            ->all();

        /*
         * Медсестёр кабинетов берём непосредственно из nurses по clinic_id.
         * user_id здесь не участвует: в старых данных он может совпадать
         * у нескольких разных записей nurses.
         */
        $nurses = Nurse::where('clinic_id', $clinic_id)
            ->select([
                'id',
                'name',
                'surname',
                'patronym',
                'profession',
            ])
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        /*
         * LEGACY: replace_nurse_id в текущей схеме хранит doctors.id.
         * Поэтому для временного замещения ищем Doctor-дубль медсестры,
         * но сопоставляем его по точному ФИО, а не по user_id.
         * Это не влияет на основной список медсестёр кабинета.
         */
        $replacementNurses = Doctor::where(
            'clinic_id',
            $clinic_id
        )
            ->whereIn(
                'profession',
                [
                    'Старшая медицинская сестра',
                    'Медицинская сестра',
                    'Процедурная медицинская сестра',
                ]
            )
            ->select([
                'id',
                'name',
                'surname',
                'patronym',
                'status',
                'profession',
            ])
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $replacementNurseDoctorIds = $replacementNurses
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $replacementDoctors = $doctors->values();

        $normalizeEmployeeName = static function (
            $surname,
            $name,
            $patronym
        ): string {
            return mb_strtolower(
                trim(
                    preg_replace(
                        '/\\s+/u',
                        ' ',
                        implode(' ', array_filter([
                            $surname,
                            $name,
                            $patronym,
                        ], static fn ($value): bool =>
                            $value !== null
                            && trim((string) $value) !== ''
                        ))
                    )
                )
            );
        };

        $replacementNurseDoctorIdsByName = $replacementNurses
            ->mapWithKeys(
                static function (Doctor $nurseDoctor) use (
                    $normalizeEmployeeName
                ): array {
                    $key = $normalizeEmployeeName(
                        $nurseDoctor->surname,
                        $nurseDoctor->name,
                        $nurseDoctor->patronym
                    );

                    return $key !== ''
                        ? [$key => (int) $nurseDoctor->id]
                        : [];
                }
            );

        $nurseDoctorMap = $nurses
            ->mapWithKeys(
                static function (Nurse $nurse) use (
                    $replacementNurseDoctorIdsByName,
                    $normalizeEmployeeName
                ): array {
                    $key = $normalizeEmployeeName(
                        $nurse->surname,
                        $nurse->name,
                        $nurse->patronym
                    );

                    $doctorId = $key !== ''
                        ? $replacementNurseDoctorIdsByName->get($key)
                        : null;

                    return $doctorId
                        ? [(int) $nurse->id => (int) $doctorId]
                        : [];
                }
            )
            ->all();

        $staffAssignments = ClinicCabinet::where(
            'clinic_id',
            $clinic_id
        )
            ->where(
                function ($query): void {
                    $query
                        ->whereNotNull('doctor_id')
                        ->orWhereNotNull('nurse_id');
                }
            )
            ->get([
                'id',
                'cabinet',
                'name',
                'cabinet_type',
                'doctor_id',
                'nurse_id',
            ]);

        $doctorAssignments = $staffAssignments
            ->whereNotNull('doctor_id')
            ->keyBy('doctor_id')
            ->map(
                static function (
                    ClinicCabinet $cabinet
                ): array {
                    return [
                        'id' => $cabinet->id,
                        'number' => $cabinet->cabinet,
                        'name' => $cabinet->name,
                    ];
                }
            )
            ->all();

        $nurseAssignments = $staffAssignments
            ->whereNotNull('nurse_id')
            ->keyBy('nurse_id')
            ->map(
                static function (
                    ClinicCabinet $cabinet
                ): array {
                    return [
                        'id' => $cabinet->id,
                        'number' => $cabinet->cabinet,
                        'name' => $cabinet->name,
                    ];
                }
            )
            ->all();

        /*
         * Один сотрудник может быть замещающим только в одном кабинете.
         * Основной кабинет сотрудника при этом не блокирует замещение.
         */
        $replacementAssignments = ClinicCabinet::where(
            'clinic_id',
            $clinic_id
        )
            ->where(
                static function ($query): void {
                    $query
                        ->whereNotNull('replace_doctor_id')
                        ->orWhereNotNull('replace_nurse_id');
                }
            )
            ->get([
                'id',
                'cabinet',
                'name',
                'replace_doctor_id',
                'replace_nurse_id',
            ]);

        $replacementDoctorAssignments = $replacementAssignments
            ->whereNotNull('replace_doctor_id')
            ->keyBy('replace_doctor_id')
            ->map(
                static function (
                    ClinicCabinet $cabinet
                ): array {
                    return [
                        'id' => $cabinet->id,
                        'number' => $cabinet->cabinet,
                        'name' => $cabinet->name,
                    ];
                }
            )
            ->all();

        $replacementNurseAssignments = $replacementAssignments
            ->whereNotNull('replace_nurse_id')
            ->keyBy('replace_nurse_id')
            ->map(
                static function (
                    ClinicCabinet $cabinet
                ): array {
                    return [
                        'id' => $cabinet->id,
                        'number' => $cabinet->cabinet,
                        'name' => $cabinet->name,
                    ];
                }
            )
            ->all();

        /*
         * Количество медицинских карт,
         * которые сейчас находятся в кабинетах текущей клиники.
         */
        $previewCardsCount = Patient::whereIn(
            'cabinet_id',
            ClinicCabinet::where(
                'clinic_id',
                $clinic_id
            )
                ->where(
                    'cabinet_type',
                    ClinicCabinet::TYPE_DOCTOR
                )
                ->when(
                    $selectedBuildingId,
                    static function (
                        $query,
                        int $buildingId
                    ): void {
                        $query->where(
                            'building_id',
                            $buildingId
                        );
                    }
                )
                ->pluck('id')
        )->count();

        $functionalMethods = ClinicCabinet::FUNCTIONAL_METHODS;

        return view('doctors.settings.offices', compact(
            'cabinets',
            'buildings',
            'selectedBuildingId',
            'selectedBuilding',
            'previewCardsCount',
            'doctors',
            'nurses',
            'procedureWeekDays',
            'doctorScheduleMap',
            'doctorAssignments',
            'nurseAssignments',
            'replacementDoctorAssignments',
            'replacementNurseAssignments',
            'nurseDoctorMap',
            'functionalMethods',
            'replacementDoctors',
            'replacementNurses'
        ));
    }

    public function storeBuilding(
        Request $request
    ) {
        $doctor = auth()->user()?->doctor;

        /*
         * Доступ к настройкам уже ограничен middleware doctors.
         * Здесь достаточно убедиться, что пользователь связан
         * с сотрудником клиники.
         */
        abort_unless(
            $doctor
                && (int) $doctor->clinic_id > 0,
            403
        );

        $clinicId = (int) $doctor->clinic_id;

        $validated = $request->validateWithBag(
            'createBuilding',
            [
                'building_number' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        'clinic_buildings',
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
                        'clinic_buildings',
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
                    'Укажите номер корпуса.',
                'building_number.unique' =>
                    'Корпус с таким номером уже существует.',
                'building_number.max' =>
                    'Номер корпуса не должен превышать 50 символов.',

                'building_name.required' =>
                    'Укажите название корпуса.',
                'building_name.unique' =>
                    'Корпус с таким названием уже существует.',

                'building_address.max' =>
                    'Адрес корпуса не должен превышать 255 символов.',
            ]
        );

        ClinicBuilding::create([
            'clinic_id' => $clinicId,
            'number' => trim(
                (string) $validated['building_number']
            ),
            'name' => trim(
                (string) $validated['building_name']
            ),
            'address' => !empty(
                $validated['building_address']
            )
                ? trim(
                    (string) $validated['building_address']
                )
                : null,
            'enabled' => true,
        ]);

        return back()->with(
            'building_success',
            'Корпус был успешно создан.'
        );
    }

    public function updateBuilding(
        Request $request
    ) {
        $doctor = auth()->user()?->doctor;

        /*
         * Доступ к настройкам уже ограничен middleware doctors.
         * Здесь достаточно убедиться, что пользователь связан
         * с сотрудником клиники.
         */
        abort_unless(
            $doctor
                && (int) $doctor->clinic_id > 0,
            403
        );

        $clinicId = (int) $doctor->clinic_id;

        $buildingId = $request->integer(
            'edit_building_id'
        );

        $building = ClinicBuilding::where(
            'clinic_id',
            $clinicId
        )
            ->where('id', $buildingId)
            ->firstOrFail();

        $validated = $request->validateWithBag(
            'editBuilding',
            [
                'edit_building_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'clinic_buildings',
                        'id'
                    )->where(
                        'clinic_id',
                        $clinicId
                    ),
                ],
                'edit_building_number' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        'clinic_buildings',
                        'number'
                    )
                        ->where(
                            'clinic_id',
                            $clinicId
                        )
                        ->ignore($building->id),
                ],
                'edit_building_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique(
                        'clinic_buildings',
                        'name'
                    )
                        ->where(
                            'clinic_id',
                            $clinicId
                        )
                        ->ignore($building->id),
                ],
                'edit_building_address' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'edit_building_id.required' =>
                    'Не удалось определить редактируемый корпус.',
                'edit_building_id.exists' =>
                    'Редактируемый корпус не относится к текущей клинике.',

                'edit_building_number.required' =>
                    'Укажите номер корпуса.',
                'edit_building_number.unique' =>
                    'Корпус с таким номером уже существует.',
                'edit_building_number.max' =>
                    'Номер корпуса не должен превышать 50 символов.',

                'edit_building_name.required' =>
                    'Укажите название корпуса.',
                'edit_building_name.unique' =>
                    'Корпус с таким названием уже существует.',

                'edit_building_address.max' =>
                    'Адрес корпуса не должен превышать 255 символов.',
            ]
        );

        $building->number = trim(
            (string) $validated['edit_building_number']
        );

        $building->name = trim(
            (string) $validated['edit_building_name']
        );

        $building->address = !empty(
            $validated['edit_building_address']
        )
            ? trim(
                (string) $validated['edit_building_address']
            )
            : null;

        $building->save();

        return back()->with(
            'building_success',
            'Корпус был успешно обновлён.'
        );
    }

    public function deleteBuilding(
        Request $request
    ) {
        $doctor = auth()->user()?->doctor;

        abort_unless(
            $doctor
                && (int) $doctor->clinic_id > 0,
            403
        );

        $clinicId = (int) $doctor->clinic_id;

        $validated = $request->validateWithBag(
            'deleteBuilding',
            [
                'delete_building_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'clinic_buildings',
                        'id'
                    )->where(
                        'clinic_id',
                        $clinicId
                    ),
                ],
            ],
            [
                'delete_building_id.required' =>
                    'Не удалось определить удаляемый корпус.',
                'delete_building_id.exists' =>
                    'Корпус не относится к текущей клинике.',
            ]
        );

        $building = ClinicBuilding::where(
            'clinic_id',
            $clinicId
        )
            ->where(
                'id',
                $validated['delete_building_id']
            )
            ->firstOrFail();

        $cabinetCount = $building
            ->cabinets()
            ->count();

        if ($cabinetCount > 0) {
            return back()->withErrors(
                [
                    'delete_building_id' =>
                        'Нельзя удалить корпус, пока в нём есть кабинеты. '
                        . 'Сначала перенесите или удалите '
                        . $cabinetCount
                        . ' кабинет(ов).',
                ],
                'deleteBuilding'
            );
        }

        $buildingLabel =
            'Корпус №'
            . $building->number
            . ' — '
            . $building->name;

        $building->delete();

        return redirect()
            ->route('doctors.settings.offices')
            ->with(
                'building_success',
                $buildingLabel
                . ' был успешно удалён.'
            );
    }

    public function deleteCabinet(
        Request $request
    ) {
        /*
         * Берём clinic_id тем же способом, что и index().
         * Здесь не нужна отдельная abort_unless(..., 403):
         * доступ к doctors/settings уже защищён middleware группы.
         */
        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $validated = $request->validateWithBag(
            'deleteOffice',
            [
                'delete_office_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'clinic_cabinets',
                        'id'
                    )->where(
                        'clinic_id',
                        $clinicId
                    ),
                ],
            ],
            [
                'delete_office_id.required' =>
                    'Не удалось определить удаляемый кабинет.',
                'delete_office_id.exists' =>
                    'Кабинет не относится к текущей клинике.',
            ]
        );

        $cabinet = ClinicCabinet::where(
            'clinic_id',
            $clinicId
        )
            ->where(
                'id',
                $validated['delete_office_id']
            )
            ->firstOrFail();

        $cabinetLabel =
            'Кабинет №'
            . $cabinet->cabinet
            . (
                $cabinet->name
                    ? ' — ' . $cabinet->name
                    : ''
            );

        DB::transaction(
            function () use (
                $cabinet,
                $clinicId
            ): void {
                /*
                 * По принятой в проекте логике карта считается
                 * не привязанной ни к какому кабинету при clinic_id = null.
                 *
                 * cabinet_id также очищаем, чтобы не оставлять ссылку
                 * на удалённый clinic_cabinets.id.
                 */
                Patient::where(
                    'cabinet_id',
                    $cabinet->id
                )
                    ->update([
                        'clinic_id' => null,
                        'cabinet_id' => null,
                    ]);

                /*
                 * Расписание процедурного кабинета относится именно
                 * к кабинету, поэтому удаляем его вместе с кабинетом.
                 * График врача/медсестры в EmployeeWorkSchedule
                 * не удаляем: он относится к сотруднику, а не к кабинету.
                 */
                $cabinet
                    ->procedureSchedules()
                    ->delete();

                $cabinet->delete();
            }
        );

        return redirect()
            ->route(
                'doctors.settings.offices',
                $request->filled('building_id')
                    ? [
                        'building_id' =>
                            $request->integer(
                                'building_id'
                            ),
                    ]
                    : []
            )
            ->with(
                'success',
                $cabinetLabel
                . ' был успешно удалён.'
            );
    }

    public function store(
        ClinicOfficeStoreRequest $request
    ) {
        abort_unless(
            $request->input('mode') === 'create',
            422
        );

        return $this->saveOffice($request);
    }

    public function clinicDoctorUpdate(
        ClinicOfficeStoreRequest $request
    ) {
        abort_unless(
            $request->input('mode') === 'edit',
            422
        );

        return $this->saveOffice($request);
    }

    public function districts()
    {
        $clinicId = (int) auth()->user()->doctor->clinic_id;

        $districtSchemaReady = $this->districtSchemaReady();
        $transferSchemaReady = $this->districtTransferSchemaReady();
        $districtDeleteReady = $districtSchemaReady;

        if ($transferSchemaReady) {
            $this->expireDistrictTransfers($clinicId);
        }

        if ($districtSchemaReady) {
            $query = ClinicDistrict::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->with([
                    'doctor:id,name,surname,patronym,status,profession',
                    'homeVisitDoctor:id,name,surname,patronym,status,profession',
                ]);

            if ($transferSchemaReady) {
                $query->with([
                    'transferredToDistrict:id,clinic_id,name,code,doctor_id',
                    'transferredToDistrict.doctor:id,name,surname,patronym,status,profession',
                ]);
            }

            $records = $query
                ->orderBy('code')
                ->orderBy('id')
                ->get();
        } else {
            $records = collect();
        }

        $additionalDoctorsByDistrict = $this->districtAdditionalDoctorsForClinic(
            $clinicId
        );

        $incomingTransfers = collect();

        if ($transferSchemaReady) {
            $incomingTransfers = $records
                ->filter(
                    fn (ClinicDistrict $district): bool =>
                        $this->districtTransferIsActive($district)
                )
                ->groupBy(
                    fn (ClinicDistrict $district): int =>
                        (int) $district->transfer_to_district_id
                );
        }

        $districts = $records
            ->map(
                fn (ClinicDistrict $district): array =>
                    $this->districtCardData(
                        $district,
                        $incomingTransfers->get(
                            (int) $district->id,
                            collect()
                        ),
                        $transferSchemaReady,
                        $additionalDoctorsByDistrict->get(
                            (int) $district->id,
                            collect()
                        )
                    )
            )
            ->all();

        $assignedDoctorIds = $records
            ->pluck('doctor_id')
            ->merge($records->pluck('home_visit_doctor_id'))
            ->merge(
                $additionalDoctorsByDistrict
                    ->flatten(1)
                    ->pluck('doctor_id')
            )
            ->filter()
            ->unique()
            ->count();

        $districtSummary = [
            'total' => $records->count(),
            'doctors' => $assignedDoctorIds,
            'transfers' => $transferSchemaReady
                ? $records
                    ->filter(
                        fn (ClinicDistrict $district): bool =>
                            $this->districtTransferIsActive($district)
                    )
                    ->count()
                : 0,
        ];

        $districtDoctors = $this->getDistrictDoctorsForCurrentClinic();
        $districtAddressSearchUrl = url('/api/addresses');
        $districtStoreUrl = url('/doctors/settings/districts');
        $districtUpdateUrlTemplate = url(
            '/doctors/settings/districts/__DISTRICT__'
        );

        return view(
            'doctors.settings.districts',
            compact(
                'districts',
                'districtSummary',
                'districtDoctors',
                'districtAddressSearchUrl',
                'districtStoreUrl',
                'districtUpdateUrlTemplate',
                'transferSchemaReady',
                'districtDeleteReady'
            )
        );
    }

    public function storeDistrict(Request $request)
    {
        return $this->saveDistrict($request);
    }

    public function updateDistrict(
        Request $request,
        int $district
    ) {
        return $this->saveDistrict($request, $district);
    }

    public function transferDistrict(
        Request $request,
        int $district
    ) {
        abort_unless(
            $this->districtTransferSchemaReady(),
            503,
            'Схема временной передачи участков не обновлена. Выполните миграцию передачи участков.'
        );

        $clinicId = (int) auth()->user()->doctor->clinic_id;

        $input = Validator::make(
            $request->all(),
            [
                'doctor_id' => ['required', 'integer'],
                'transfer_from' => ['required', 'date'],
                'transfer_until' => [
                    'required',
                    'date',
                    'after_or_equal:transfer_from',
                    'after_or_equal:today',
                ],
                'reason' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'doctor_id.required' => 'Выберите врача другого участка.',
                'transfer_from.required' => 'Укажите дату начала передачи.',
                'transfer_until.required' => 'Укажите дату окончания передачи.',
                'transfer_until.after_or_equal' => 'Дата окончания передачи не может быть раньше даты начала и сегодняшней даты.',
            ]
        )->validate();

        $result = DB::transaction(
            function () use (
                $clinicId,
                $district,
                $input
            ): array {
                DB::table('clinics')
                    ->where('id', $clinicId)
                    ->lockForUpdate()
                    ->first();

                $source = ClinicDistrict::query()
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->lockForUpdate()
                    ->findOrFail($district);

                if ($source->transfer_to_district_id) {
                    throw ValidationException::withMessages([
                        'form' => 'Этот участок уже передан другому участку. Сначала сепаратируйте участки.',
                    ]);
                }

                $incomingToSource = ClinicDistrict::query()
                    ->where('clinic_id', $clinicId)
                    ->where('transfer_to_district_id', $source->id)
                    ->lockForUpdate()
                    ->first();

                if ($incomingToSource) {
                    throw ValidationException::withMessages([
                        'form' => 'Этот участок уже принимает другой участок и не может быть передан дальше.',
                    ]);
                }

                $target = ClinicDistrict::query()
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->where('doctor_id', (int) $input['doctor_id'])
                    ->where('id', '<>', $source->id)
                    ->lockForUpdate()
                    ->first();

                if (!$target) {
                    throw ValidationException::withMessages([
                        'doctor_id' => 'Выбранный врач не является участковым психиатром другого участка.',
                    ]);
                }

                if ($target->transfer_to_district_id) {
                    throw ValidationException::withMessages([
                        'doctor_id' => sprintf(
                            'Участок «%s» сам передан другому участку и сейчас недоступен для объединения.',
                            $target->name
                        ),
                    ]);
                }

                $incomingToTarget = ClinicDistrict::query()
                    ->where('clinic_id', $clinicId)
                    ->where('transfer_to_district_id', $target->id)
                    ->where('id', '<>', $source->id)
                    ->lockForUpdate()
                    ->first();

                if ($incomingToTarget) {
                    throw ValidationException::withMessages([
                        'doctor_id' => sprintf(
                            'Участок «%s» уже временно обслуживает участок «%s».',
                            $target->name,
                            $incomingToTarget->name
                        ),
                    ]);
                }

                $source->transfer_to_district_id = (int) $target->id;
                $source->transfer_from = $input['transfer_from'];
                $source->transfer_until = $input['transfer_until'];
                $source->transfer_reason = trim(
                    (string) ($input['reason'] ?? '')
                ) ?: null;
                $source->save();

                return [
                    'source' => $source->name,
                    'target' => $target->name,
                ];
            },
            3
        );

        return response()->json([
            'ok' => true,
            'message' => sprintf(
                'Участок «%s» временно передан участку «%s».',
                $result['source'],
                $result['target']
            ),
        ]);
    }

    public function separateDistrict(
        Request $request,
        int $district
    ) {
        abort_unless(
            $this->districtTransferSchemaReady(),
            503,
            'Схема временной передачи участков не обновлена.'
        );

        $clinicId = (int) auth()->user()->doctor->clinic_id;

        DB::transaction(
            function () use (
                $clinicId,
                $district
            ): void {
                DB::table('clinics')
                    ->where('id', $clinicId)
                    ->lockForUpdate()
                    ->first();

                $source = ClinicDistrict::query()
                    ->where('clinic_id', $clinicId)
                    ->lockForUpdate()
                    ->findOrFail($district);

                $source->transfer_to_district_id = null;
                $source->transfer_from = null;
                $source->transfer_until = null;
                $source->transfer_reason = null;
                $source->save();
            },
            3
        );

        return response()->json([
            'ok' => true,
            'message' => 'Участки сепаратированы. Участок снова обслуживается самостоятельно.',
        ]);
    }

    public function deleteDistrict(
        Request $request,
        int $district
    ) {
        abort_unless(
            $this->districtSchemaReady(),
            503,
            'Схема таблицы участков не обновлена.'
        );

        $clinicId = (int) auth()->user()->doctor->clinic_id;

        $result = DB::transaction(
            function () use (
                $clinicId,
                $district
            ): array {
                DB::table('clinics')
                    ->where('id', $clinicId)
                    ->lockForUpdate()
                    ->first();

                $source = ClinicDistrict::query()
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->lockForUpdate()
                    ->findOrFail($district);

                /*
                 * Переданный участок удалять напрямую не даём: на его карточке
                 * по UX остаётся только действие «Сепаратировать участки».
                 */
                if (
                    $this->districtTransferSchemaReady()
                    && $source->transfer_to_district_id
                ) {
                    throw ValidationException::withMessages([
                        'district' => 'Этот участок сейчас временно передан другому участку. Сначала сепаратируйте участки.',
                    ]);
                }

                $separatedIncomingCount = 0;

                if ($this->districtTransferSchemaReady()) {
                    /*
                     * Если НА удаляемый участок направлено временное
                     * обслуживание других участков — прекращаем его.
                     * Переданные участки снова становятся самостоятельными.
                     */
                    $incomingSources = ClinicDistrict::query()
                        ->where('clinic_id', $clinicId)
                        ->where('transfer_to_district_id', $source->id)
                        ->lockForUpdate()
                        ->get();

                    foreach ($incomingSources as $incomingSource) {
                        $incomingSource->transfer_to_district_id = null;
                        $incomingSource->transfer_from = null;
                        $incomingSource->transfer_until = null;
                        $incomingSource->transfer_reason = null;
                        $incomingSource->save();

                        $separatedIncomingCount++;
                    }
                }

                /*
                 * Если дополнительная таблица уже была создана предыдущей
                 * версией — просто очищаем связи удаляемого участка.
                 * Врачи никуда не переносятся.
                 */
                if (Schema::hasTable('clinic_district_doctors')) {
                    DB::table('clinic_district_doctors')
                        ->where('clinic_id', $clinicId)
                        ->where('district_id', $source->id)
                        ->delete();
                }

                $sourceName = (string) $source->name;

                /*
                 * doctor_id и home_visit_doctor_id являются привязками самого
                 * участка. После удаления строки участка врачи остаются в
                 * doctors и продолжают быть сотрудниками клиники.
                 */
                $source->delete();

                return [
                    'source' => $sourceName,
                    'separated_incoming' => $separatedIncomingCount,
                ];
            },
            3
        );

        return response()->json([
            'ok' => true,
            'message' => sprintf(
                'Участок «%s» удалён.%s Врачи остались сотрудниками клиники и больше не закреплены за этим участком.',
                $result['source'],
                $result['separated_incoming'] > 0
                    ? ' Временное обслуживание направленных на него участков прекращено.'
                    : ''
            ),
        ]);
    }

    private function saveDistrict(
        Request $request,
        ?int $districtId = null
    ) {
        abort_unless(
            $this->districtSchemaReady(),
            503,
            'Схема таблицы участков не обновлена. Выполните repair-миграцию clinic_districts.'
        );

        $clinicId = (int) auth()->user()->doctor->clinic_id;

        $input = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:50'],
                'description' => ['nullable', 'string', 'max:500'],
                'doctor_id' => [
                    'required',
                    'integer',
                ],
                'home_visit_doctor_id' => [
                    'required',
                    'integer',
                    'different:doctor_id',
                ],
                'addresses' => [
                    'required',
                    'array',
                    'min:1',
                    'max:100',
                ],
                'addresses.*.type' => [
                    'required',
                    Rule::in([
                        'street-range',
                        'street',
                        'settlement',
                        'exact',
                    ]),
                ],
                'addresses.*.address' => [
                    'required',
                    'string',
                    'max:500',
                ],
                'addresses.*.fias_id' => [
                    'required',
                    'string',
                    'max:100',
                ],
                'addresses.*.detail' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
            ],
            [
                'name.required' => 'Укажите название участка.',
                'code.required' => 'Укажите код участка.',
                'doctor_id.required' => 'Выберите участкового психиатра.',
                'home_visit_doctor_id.required' => 'Выберите психиатра для вызовов на дом.',
                'home_visit_doctor_id.different' => 'Участковый и выездной психиатр должны быть разными сотрудниками.',
                'addresses.required' => 'Добавьте территорию участка.',
                'addresses.min' => 'Добавьте хотя бы одно адресное правило.',
                'addresses.*.address.required' => 'Выберите адрес из подсказок.',
                'addresses.*.fias_id.required' => 'Выберите адрес именно из подсказок.',
            ]
        )->validate();

        $rules = $this->normalizeDistrictRules(
            array_values($input['addresses'])
        );

        $district = DB::transaction(
            function () use (
                $clinicId,
                $districtId,
                $input,
                $rules
            ): ClinicDistrict {
                DB::table('clinics')
                    ->where('id', $clinicId)
                    ->lockForUpdate()
                    ->first();

                $district = $districtId
                    ? ClinicDistrict::query()
                        ->where('clinic_id', $clinicId)
                        ->findOrFail($districtId)
                    : new ClinicDistrict();

                Validator::make(
                    $input,
                    [
                        'code' => [
                            Rule::unique('clinic_districts', 'code')
                                ->where('clinic_id', $clinicId)
                                ->ignore($district->id),
                        ],
                    ],
                    [
                        'code.unique' => 'В этой клинике уже есть участок с таким кодом.',
                    ]
                )->validate();

                $this->assertDistrictDoctorsAvailable(
                    $clinicId,
                    $district->id,
                    (int) $input['doctor_id'],
                    (int) $input['home_visit_doctor_id']
                );

                $this->assertDistrictTerritoryDoesNotOverlap(
                    $clinicId,
                    $district->id,
                    $rules
                );

                $district->fill([
                    'clinic_id' => $clinicId,
                    'name' => trim((string) $input['name']),
                    'code' => trim((string) $input['code']),
                    'description' => trim(
                        (string) ($input['description'] ?? '')
                    ) ?: null,
                    'doctor_id' => (int) $input['doctor_id'],
                    'home_visit_doctor_id' =>
                        (int) $input['home_visit_doctor_id'],
                    'address_rules' => $rules,
                    'enabled' => true,
                ]);
                $district->save();

                if (Schema::hasTable('clinic_district_doctors')) {
                    DB::table('clinic_district_doctors')
                        ->where('clinic_id', $clinicId)
                        ->where('district_id', $district->id)
                        ->whereIn('doctor_id', [
                            (int) $district->doctor_id,
                            (int) $district->home_visit_doctor_id,
                        ])
                        ->delete();
                }

                return $district;
            },
            3
        );

        return response()->json([
            'ok' => true,
            'id' => $district->id,
            'message' => 'Участок сохранён.',
        ]);
    }

    private function assertDistrictDoctorsAvailable(
        int $clinicId,
        ?int $editingDistrictId,
        int $doctorId,
        int $homeVisitDoctorId
    ): void {
        $availableIds = $this->psychiatristsForClinicQuery($clinicId)
            ->whereIn('id', [
                $doctorId,
                $homeVisitDoctorId,
            ])
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $errors = [];

        if (!in_array($doctorId, $availableIds, true)) {
            $errors['doctor_id'][] =
                'Участковый психиатр не относится к текущей клинике.';
        }

        if (!in_array($homeVisitDoctorId, $availableIds, true)) {
            $errors['home_visit_doctor_id'][] =
                'Выездной психиатр не относится к текущей клинике.';
        }

        $otherDistricts = ClinicDistrict::query()
            ->where('clinic_id', $clinicId)
            ->when(
                $editingDistrictId,
                static fn ($query) =>
                    $query->where('id', '<>', $editingDistrictId)
            )
            ->get([
                'id',
                'name',
                'doctor_id',
                'home_visit_doctor_id',
            ]);

        foreach ([
            'doctor_id' => $doctorId,
            'home_visit_doctor_id' => $homeVisitDoctorId,
        ] as $field => $candidateId) {
            $conflict = $otherDistricts->first(
                static fn (ClinicDistrict $item): bool =>
                    (int) $item->doctor_id === $candidateId
                    || (int) $item->home_visit_doctor_id === $candidateId
            );

            if ($conflict) {
                $errors[$field][] = sprintf(
                    'Врач уже закреплён за участком «%s». Один врач может быть назначен только на один участок.',
                    $conflict->name
                );
            }
        }

        if (Schema::hasTable('clinic_district_doctors')) {
            $additionalAssignments = DB::table('clinic_district_doctors as cdd')
                ->join('clinic_districts as cd', 'cd.id', '=', 'cdd.district_id')
                ->where('cdd.clinic_id', $clinicId)
                ->when(
                    $editingDistrictId,
                    static fn ($query) =>
                        $query->where('cdd.district_id', '<>', $editingDistrictId)
                )
                ->whereIn('cdd.doctor_id', [
                    $doctorId,
                    $homeVisitDoctorId,
                ])
                ->get([
                    'cdd.doctor_id',
                    'cd.name as district_name',
                ]);

            foreach ([
                'doctor_id' => $doctorId,
                'home_visit_doctor_id' => $homeVisitDoctorId,
            ] as $field => $candidateId) {
                $conflict = $additionalAssignments->first(
                    static fn ($row): bool =>
                        (int) $row->doctor_id === $candidateId
                );

                if ($conflict) {
                    $errors[$field][] = sprintf(
                        'Врач дополнительно закреплён за участком «%s».',
                        $conflict->district_name
                    );
                }
            }
        }

        /*
         * Выездной врач не должен одновременно находиться в кабинете
         * или быть назначенным замещающим врачом.
         */
        $officeConflict = ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->where(
                static function ($query) use ($homeVisitDoctorId): void {
                    $query
                        ->where('doctor_id', $homeVisitDoctorId)
                        ->orWhere(
                            'replace_doctor_id',
                            $homeVisitDoctorId
                        );
                }
            )
            ->first([
                'id',
                'cabinet',
                'name',
            ]);

        if ($officeConflict) {
            $errors['home_visit_doctor_id'][] = sprintf(
                'Этот врач уже назначен в кабинет №%s%s. Сначала уберите его из кабинета/замещения, затем назначайте на вызовы на дом.',
                $officeConflict->cabinet,
                $officeConflict->name
                    ? ' — ' . $officeConflict->name
                    : ''
            );
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function normalizeDistrictRules(array $rows): array
    {
        $rules = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $type = trim((string) ($row['type'] ?? ''));
            $address = trim((string) ($row['address'] ?? ''));
            $fiasId = trim((string) ($row['fias_id'] ?? ''));
            $detail = trim((string) ($row['detail'] ?? ''));

            $rule = [
                'type' => $type,
                'address' => $address,
                'fias_id' => $fiasId,
                'detail' => null,
                'house_from' => null,
                'house_to' => null,
                'parity' => 'all',
            ];

            if ($type === 'street-range') {
                if (!preg_match(
                    '/^(\d+)\s*[-–]\s*(\d+)(?:\s*,\s*(неч[её]тные|ч[её]тные))?$/ui',
                    $detail,
                    $matches
                )) {
                    $errors['addresses.' . $index . '.detail'][] =
                        'Укажите диапазон домов, например 1-10 или 1-55, нечётные.';
                    continue;
                }

                $from = (int) $matches[1];
                $to = (int) $matches[2];

                if ($from <= 0 || $to <= 0 || $from > $to) {
                    $errors['addresses.' . $index . '.detail'][] =
                        'Начало диапазона должно быть не больше конца.';
                    continue;
                }

                $parityLabel = mb_strtolower(
                    (string) ($matches[3] ?? '')
                );
                $parity = str_contains($parityLabel, 'неч')
                    ? 'odd'
                    : (
                        str_contains($parityLabel, 'чёт')
                        || str_contains($parityLabel, 'чет')
                            ? 'even'
                            : 'all'
                    );

                $rule['house_from'] = $from;
                $rule['house_to'] = $to;
                $rule['parity'] = $parity;
                $rule['detail'] = $from . '-' . $to
                    . ($parity === 'odd'
                        ? ', нечётные'
                        : ($parity === 'even' ? ', чётные' : ''));
            } elseif ($type === 'exact') {
                if ($detail === '') {
                    $errors['addresses.' . $index . '.detail'][] =
                        'Укажите номер дома.';
                    continue;
                }

                $rule['detail'] = $detail;
            }

            $rules[] = $rule;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $rules;
    }

    private function assertDistrictTerritoryDoesNotOverlap(
        int $clinicId,
        ?int $editingDistrictId,
        array $rules
    ): void {
        $errors = [];

        for ($left = 0; $left < count($rules); $left++) {
            for ($right = 0; $right < $left; $right++) {
                if ($this->districtRulesOverlap(
                    $rules[$left],
                    $rules[$right]
                )) {
                    $errors['addresses.' . $left][] = sprintf(
                        'Правило %d пересекается с правилом %d этого же участка.',
                        $left + 1,
                        $right + 1
                    );
                }
            }
        }

        $otherDistricts = ClinicDistrict::query()
            ->where('clinic_id', $clinicId)
            ->when(
                $editingDistrictId,
                static fn ($query) =>
                    $query->where('id', '<>', $editingDistrictId)
            )
            ->get([
                'id',
                'name',
                'code',
                'address_rules',
            ]);

        foreach ($rules as $index => $rule) {
            foreach ($otherDistricts as $district) {
                foreach (
                    (array) ($district->address_rules ?? [])
                    as $existingRule
                ) {
                    if ($this->districtRulesOverlap(
                        $rule,
                        (array) $existingRule
                    )) {
                        $errors['addresses.' . $index][] = sprintf(
                            'Территория пересекается с участком «%s» (код %s).',
                            $district->name,
                            $district->code
                        );
                        break 2;
                    }
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function districtRulesOverlap(
        array $left,
        array $right
    ): bool {
        $leftType = (string) ($left['type'] ?? '');
        $rightType = (string) ($right['type'] ?? '');
        $leftFias = (string) ($left['fias_id'] ?? '');
        $rightFias = (string) ($right['fias_id'] ?? '');

        if ($leftType === 'settlement' || $rightType === 'settlement') {
            $settlement = $leftType === 'settlement' ? $left : $right;
            $other = $leftType === 'settlement' ? $right : $left;
            $settlementAddress = $this->normalizeDistrictAddress(
                (string) ($settlement['address'] ?? '')
            );
            $otherAddress = $this->normalizeDistrictAddress(
                (string) ($other['address'] ?? '')
            );

            return $settlementAddress !== ''
                && (
                    $otherAddress === $settlementAddress
                    || str_starts_with(
                        $otherAddress,
                        $settlementAddress . ' '
                    )
                );
        }

        if (
            $leftFias === ''
            || $rightFias === ''
            || $leftFias !== $rightFias
        ) {
            return false;
        }

        if ($leftType === 'street' || $rightType === 'street') {
            return true;
        }

        if ($leftType === 'exact' && $rightType === 'exact') {
            return $this->normalizeHouseNumber(
                (string) ($left['detail'] ?? '')
            ) === $this->normalizeHouseNumber(
                (string) ($right['detail'] ?? '')
            );
        }

        if (
            $leftType === 'street-range'
            && $rightType === 'street-range'
        ) {
            $from = max(
                (int) ($left['house_from'] ?? 0),
                (int) ($right['house_from'] ?? 0)
            );
            $to = min(
                (int) ($left['house_to'] ?? 0),
                (int) ($right['house_to'] ?? 0)
            );

            if ($from > $to) {
                return false;
            }

            $leftParity = (string) ($left['parity'] ?? 'all');
            $rightParity = (string) ($right['parity'] ?? 'all');

            if ($leftParity === 'all' || $rightParity === 'all') {
                $requiredParity = $leftParity === 'all'
                    ? $rightParity
                    : $leftParity;

                if ($requiredParity === 'all') {
                    return true;
                }

                if ($from < $to) {
                    return true;
                }

                return $this->houseMatchesParity(
                    $from,
                    $requiredParity
                );
            }

            if ($leftParity !== $rightParity) {
                return false;
            }

            if ($from < $to) {
                return true;
            }

            return $this->houseMatchesParity(
                $from,
                $leftParity
            );
        }

        if (
            $leftType === 'street-range'
            || $rightType === 'street-range'
        ) {
            $range = $leftType === 'street-range' ? $left : $right;
            $exact = $leftType === 'exact' ? $left : $right;

            if ((string) ($exact['type'] ?? '') !== 'exact') {
                return false;
            }

            if (!preg_match(
                '/^(\d+)/u',
                $this->normalizeHouseNumber(
                    (string) ($exact['detail'] ?? '')
                ),
                $matches
            )) {
                return false;
            }

            $house = (int) $matches[1];

            return $house >= (int) ($range['house_from'] ?? 0)
                && $house <= (int) ($range['house_to'] ?? 0)
                && $this->houseMatchesParity(
                    $house,
                    (string) ($range['parity'] ?? 'all')
                );
        }

        return false;
    }

    private function houseMatchesParity(
        int $house,
        string $parity
    ): bool {
        return $parity === 'all'
            || ($parity === 'even' && $house % 2 === 0)
            || ($parity === 'odd' && $house % 2 === 1);
    }

    private function normalizeHouseNumber(string $value): string
    {
        return mb_strtolower(
            preg_replace('/\s+/u', '', trim($value)) ?? ''
        );
    }

    private function normalizeDistrictAddress(string $value): string
    {
        $value = mb_strtolower($value);
        $value = str_replace('ё', 'е', $value);
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value) ?? '';

        return trim(
            preg_replace('/\s+/u', ' ', $value) ?? ''
        );
    }

    private function districtCardData(
        ClinicDistrict $district,
        iterable $incomingTransfers = [],
        bool $transferSchemaReady = false,
        iterable $additionalDoctors = []
    ): array {
        $rules = collect($district->address_rules ?? [])
            ->values();
        $visible = $rules->take(3)->values();
        $remaining = $rules->slice(3)->values();
        $doctor = $district->doctor;
        $homeDoctor = $district->homeVisitDoctor;

        $doctorName = $doctor?->full_name ?: 'Врач не назначен';
        $homeDoctorName = $homeDoctor?->full_name
            ?: 'Выездной врач не назначен';

        $additionalDoctorCards = collect($additionalDoctors)
            ->map(function ($row): array {
                $name = trim(implode(' ', array_filter([
                    $row->surname ?? null,
                    $row->name ?? null,
                    $row->patronym ?? null,
                ])));

                $role = (string) ($row->role ?? 'additional');

                return [
                    'id' => (int) ($row->doctor_id ?? 0),
                    'name' => $name !== ''
                        ? $name
                        : 'Врач #' . (int) ($row->doctor_id ?? 0),
                    'initials' => mb_strtoupper(
                        mb_substr((string) ($row->name ?? ''), 0, 1)
                        . mb_substr((string) ($row->surname ?? ''), 0, 1)
                    ) ?: 'В',
                    'role' => $role,
                    'role_label' => $role === 'home'
                        ? 'Перенесённый выездной психиатр'
                        : ($role === 'main'
                            ? 'Перенесённый участковый психиатр'
                            : 'Дополнительно закреплённый врач'),
                    'speciality' => $this->cleanDoctorSpeciality(
                        (string) ($row->status ?? '')
                    ),
                    'source_district' => (string) ($row->source_district_name ?? ''),
                ];
            })
            ->values()
            ->all();

        $visibleCards = $visible
            ->map(fn (array $rule): array => [
                'text' => $this->districtRuleText($rule),
                'type' => $this->districtRuleTypeLabel(
                    (string) ($rule['type'] ?? '')
                ),
            ])
            ->all();

        $remainingCards = $remaining
            ->map(fn (array $rule): array => [
                'text' => $this->districtRuleText($rule),
                'type' => $this->districtRuleTypeLabel(
                    (string) ($rule['type'] ?? '')
                ),
            ])
            ->all();

        $transfer = null;
        $isTransferred = false;

        if (
            $transferSchemaReady
            && $district->transfer_to_district_id
        ) {
            $targetDistrict = $district->transferredToDistrict;
            $targetDoctor = $targetDistrict?->doctor;
            $isTransferred = $this->districtTransferIsActive(
                $district
            );

            $transfer = [
                'active' => $isTransferred,
                'scheduled' => !$isTransferred
                    && $district->transfer_from
                    && $district->transfer_from->isFuture(),
                'target_district_id' =>
                    (int) $district->transfer_to_district_id,
                'district' => $targetDistrict?->name
                    ?: 'Другой участок',
                'district_code' => $targetDistrict?->code ?: '',
                'doctor' => $targetDoctor?->full_name ?: '',
                'dates' => trim(
                    ($district->transfer_from
                        ? $district->transfer_from->format('d.m.Y')
                        : '—')
                    . ' — '
                    . ($district->transfer_until
                        ? $district->transfer_until->format('d.m.Y')
                        : '—')
                ),
                'reason' => $district->transfer_reason
                    ?: 'Причина не указана',
            ];
        }

        $incomingTransfer = collect($incomingTransfers)
            ->filter(
                fn (ClinicDistrict $source): bool =>
                    $this->districtTransferIsActive($source)
            )
            ->first();

        $incoming = $incomingTransfer
            ? [
                'source_district_id' => (int) $incomingTransfer->id,
                'district' => $incomingTransfer->name,
                'district_code' => $incomingTransfer->code,
                'dates' => trim(
                    ($incomingTransfer->transfer_from
                        ? $incomingTransfer->transfer_from->format('d.m.Y')
                        : '—')
                    . ' — '
                    . ($incomingTransfer->transfer_until
                        ? $incomingTransfer->transfer_until->format('d.m.Y')
                        : '—')
                ),
            ]
            : null;

        return [
            'id' => $district->id,
            'name' => $district->name,
            'code' => $district->code,
            'description' => $district->description ?: '',
            'doctor' => $doctorName,
            'doctor_id' => $district->doctor_id,
            'speciality' => $doctor
                ? $this->cleanDoctorSpeciality(
                    (string) ($doctor->status ?? '')
                )
                : '',
            'initials' => $this->doctorInitials($doctor),
            'home_visit_doctor' => $homeDoctorName,
            'home_visit_doctor_id' =>
                $district->home_visit_doctor_id,
            'home_visit_speciality' => $homeDoctor
                ? $this->cleanDoctorSpeciality(
                    (string) ($homeDoctor->status ?? '')
                )
                : '',
            'home_visit_initials' =>
                $this->doctorInitials($homeDoctor),
            'additional_doctors' => $additionalDoctorCards,
            'transfer' => $transfer,
            'is_transferred' => $isTransferred,
            'incoming_transfer' => $incoming,
            'search_text' => mb_strtolower(
                $district->name
                . ' '
                . $doctorName
                . ' '
                . $homeDoctorName
                . ' '
                . ($transfer['district'] ?? '')
                . ' '
                . ($incoming['district'] ?? '')
                . ' '
                . collect($additionalDoctorCards)
                    ->pluck('name')
                    ->implode(' ')
                . ' '
                . $rules
                    ->map(fn (array $rule): string =>
                        $this->districtRuleText($rule)
                    )
                    ->implode(' ')
            ),
            'visible_addresses' => $visibleCards,
            'remaining_addresses' => $remainingCards,
            'has_more_addresses' => $remaining->isNotEmpty(),
            'has_remaining_addresses' => $remaining->isNotEmpty(),
            'remaining_addresses_count' => $remaining->count(),
            'total_addresses_label' => $rules->count()
                . ' '
                . $this->districtRuleWord(
                    $rules->count(),
                    true
                ),
            'more_addresses_label' => '+ ещё '
                . $remaining->count()
                . ' '
                . $this->districtRuleWord($remaining->count()),
            'visible_addresses_label' => $visible->count()
                . ' '
                . $this->districtRuleWord($visible->count()),
            'missing_addresses_label' => '0 правил',
            'rules_json' => json_encode(
                $rules->all(),
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) ?: '[]',
        ];
    }

    private function districtRuleText(array $rule): string
    {
        $address = (string) ($rule['address'] ?? '');
        $detail = (string) ($rule['detail'] ?? '');

        return match ((string) ($rule['type'] ?? '')) {
            'street-range' => $address . ' — дома ' . $detail,
            'street' => $address . ' — полностью',
            'settlement' => $address . ' — полностью',
            'exact' => $address . ', дом ' . $detail,
            default => $address,
        };
    }

    private function districtRuleTypeLabel(string $type): string
    {
        return match ($type) {
            'street-range' => 'Диапазон домов',
            'street' => 'Улица целиком',
            'settlement' => 'Населённый пункт',
            'exact' => 'Конкретный адрес',
            default => 'Адресное правило',
        };
    }

    private function districtAdditionalDoctorsForClinic(int $clinicId)
    {
        if (!Schema::hasTable('clinic_district_doctors')) {
            return collect();
        }

        return DB::table('clinic_district_doctors as cdd')
            ->join('doctors as d', 'd.id', '=', 'cdd.doctor_id')
            ->where('cdd.clinic_id', $clinicId)
            ->select([
                'cdd.district_id',
                'cdd.doctor_id',
                'cdd.role',
                'cdd.source_district_name',
                'd.name',
                'd.surname',
                'd.patronym',
                'd.status',
                'd.profession',
            ])
            ->orderBy('d.surname')
            ->orderBy('d.name')
            ->get()
            ->groupBy(
                static fn ($row): int => (int) $row->district_id
            );
    }

    private function getDistrictDoctorsForCurrentClinic(): array
    {
        $clinicId = (int) auth()->user()->doctor->clinic_id;

        $assignments = ClinicDistrict::query()
            ->where('clinic_id', $clinicId)
            ->get([
                'id',
                'name',
                'code',
                'doctor_id',
                'home_visit_doctor_id',
            ]);

        $assignmentMap = [];

        foreach ($assignments as $district) {
            if ($district->doctor_id) {
                $assignmentMap[(int) $district->doctor_id] = [
                    'district_id' => (int) $district->id,
                    'district_name' => (string) $district->name,
                    'district_code' => (string) $district->code,
                    'role' => 'main',
                ];
            }

            if ($district->home_visit_doctor_id) {
                $assignmentMap[
                    (int) $district->home_visit_doctor_id
                ] = [
                    'district_id' => (int) $district->id,
                    'district_name' => (string) $district->name,
                    'district_code' => (string) $district->code,
                    'role' => 'home',
                ];
            }
        }

        if (Schema::hasTable('clinic_district_doctors')) {
            $additionalAssignments = DB::table('clinic_district_doctors as cdd')
                ->join('clinic_districts as cd', 'cd.id', '=', 'cdd.district_id')
                ->where('cdd.clinic_id', $clinicId)
                ->get([
                    'cdd.doctor_id',
                    'cdd.role',
                    'cd.id as district_id',
                    'cd.name as district_name',
                    'cd.code as district_code',
                ]);

            foreach ($additionalAssignments as $assignment) {
                $assignmentMap[(int) $assignment->doctor_id] = [
                    'district_id' => (int) $assignment->district_id,
                    'district_name' => (string) $assignment->district_name,
                    'district_code' => (string) $assignment->district_code,
                    'role' => 'additional_' . (string) ($assignment->role ?: 'doctor'),
                ];
            }
        }

        $officeDoctorIds = ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->get([
                'doctor_id',
                'replace_doctor_id',
            ])
            ->flatMap(
                static fn (ClinicCabinet $cabinet): array => [
                    $cabinet->doctor_id,
                    $cabinet->replace_doctor_id,
                ]
            )
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->psychiatristsForClinicQuery($clinicId)
            ->select([
                'id',
                'name',
                'surname',
                'patronym',
                'profession',
                'status',
            ])
            ->orderBy('surname')
            ->orderBy('name')
            ->get()
            ->map(
                function (Doctor $doctor) use (
                    $assignmentMap,
                    $officeDoctorIds
                ): array {
                    $assignment = $assignmentMap[
                        (int) $doctor->id
                    ] ?? null;

                    return [
                        'id' => (int) $doctor->id,
                        'name' => $doctor->full_name
                            ?: 'Врач #' . $doctor->id,
                        'profession' => trim(
                            (string) (
                                $doctor->profession
                                ?: 'Врач'
                            )
                        ),
                        'speciality' =>
                            $this->cleanDoctorSpeciality(
                                (string) ($doctor->status ?? '')
                            ),
                        'initials' =>
                            $this->doctorInitials($doctor),
                        'assigned_district_id' =>
                            $assignment['district_id'] ?? null,
                        'assigned_role' =>
                            $assignment['role'] ?? null,
                        'assigned_district_name' =>
                            $assignment['district_name'] ?? null,
                        'assigned_district_code' =>
                            $assignment['district_code'] ?? null,
                        'home_eligible' =>
                            !in_array(
                                (int) $doctor->id,
                                $officeDoctorIds,
                                true
                            ),
                    ];
                }
            )
            ->values()
            ->all();
    }

    private function doctorInitials(?Doctor $doctor): string
    {
        if (!$doctor) {
            return '—';
        }

        $initials = collect([
            $doctor->name,
            $doctor->surname,
        ])
            ->filter()
            ->map(static fn ($part): string =>
                mb_strtoupper(
                    mb_substr((string) $part, 0, 1)
                )
            )
            ->implode('');

        return $initials !== '' ? $initials : 'В';
    }

    private function cleanDoctorSpeciality(string $value): string
    {
        return collect(
            preg_split(
                '/\s*[,;|]\s*/u',
                $value,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: []
        )
            ->map(static fn ($item): string =>
                trim((string) $item)
            )
            ->filter()
            ->reject(
                static fn (string $item): bool =>
                    in_array(
                        mb_strtolower($item),
                        [
                            'невролог',
                            'неврология',
                        ],
                        true
                    )
            )
            ->unique()
            ->values()
            ->implode(', ');
    }

    private function clinicDoctorsQuery(int $clinicId)
    {
        return Doctor::query()
            ->where('clinic_id', $clinicId)
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
            );
    }

    private function psychiatristsForClinicQuery(int $clinicId)
    {
        return $this->clinicDoctorsQuery($clinicId)
            ->where(
                static function ($query): void {
                    $query
                        ->whereRaw(
                            "LOWER(COALESCE(status, '')) LIKE ?",
                            ['%психиатр%']
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(profession, '')) LIKE ?",
                            ['%психиатр%']
                        );
                }
            );
    }

    private function districtSchemaReady(): bool
    {
        if (!Schema::hasTable('clinic_districts')) {
            return false;
        }

        foreach ([
            'clinic_id',
            'code',
            'name',
            'doctor_id',
            'home_visit_doctor_id',
            'address_rules',
            'enabled',
        ] as $column) {
            if (!Schema::hasColumn('clinic_districts', $column)) {
                return false;
            }
        }

        return true;
    }

    private function districtTransferSchemaReady(): bool
    {
        if (!$this->districtSchemaReady()) {
            return false;
        }

        foreach ([
            'transfer_to_district_id',
            'transfer_from',
            'transfer_until',
            'transfer_reason',
        ] as $column) {
            if (!Schema::hasColumn('clinic_districts', $column)) {
                return false;
            }
        }

        return true;
    }

    private function districtTransferIsActive(
        ClinicDistrict $district
    ): bool {
        if (!$district->transfer_to_district_id) {
            return false;
        }

        $today = now()->startOfDay();
        $from = $district->transfer_from;
        $until = $district->transfer_until;

        return (!$from || $from->startOfDay()->lte($today))
            && (!$until || $until->endOfDay()->gte($today));
    }

    private function expireDistrictTransfers(int $clinicId): void
    {
        if (!$this->districtTransferSchemaReady()) {
            return;
        }

        DB::table('clinic_districts')
            ->where('clinic_id', $clinicId)
            ->whereNotNull('transfer_to_district_id')
            ->whereNotNull('transfer_until')
            ->whereDate('transfer_until', '<', now()->toDateString())
            ->update([
                'transfer_to_district_id' => null,
                'transfer_from' => null,
                'transfer_until' => null,
                'transfer_reason' => null,
                'updated_at' => now(),
            ]);
    }

    private function homeVisitDoctorIdsForClinic(
        int $clinicId
    ): array {
        if (!$this->districtSchemaReady()) {
            return [];
        }

        $ids = DB::table('clinic_districts')
            ->where('clinic_id', $clinicId)
            ->whereNotNull('home_visit_doctor_id')
            ->pluck('home_visit_doctor_id');

        if (Schema::hasTable('clinic_district_doctors')) {
            $ids = $ids->merge(
                DB::table('clinic_district_doctors')
                    ->where('clinic_id', $clinicId)
                    ->where('role', 'home')
                    ->pluck('doctor_id')
            );
        }

        return $ids
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function districtRuleWord(
        int $count,
        bool $withAddress = false
    ): string {
        $forms = $withAddress
            ? ['адресное правило', 'адресных правила', 'адресных правил']
            : ['правило', 'правила', 'правил'];

        $lastTwoDigits = $count % 100;
        $lastDigit = $count % 10;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return $forms[2];
        }

        if ($lastDigit === 1) {
            return $forms[0];
        }

        return $lastDigit >= 2 && $lastDigit <= 4
            ? $forms[1]
            : $forms[2];
    }

    private function getDistrictsPreviewData(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Участок №1 — Каштак',
                'code' => '01',
                'description' => 'Основной городской участок',
                'doctor' => 'Иванов Иван Иванович',
                'speciality' => 'Врач-психиатр участковый',
                'initials' => 'ИИ',
                'addresses' => [
                    ['text' => 'г. Томск, ул. Говорова — дома 1–47', 'type' => 'Диапазон домов'],
                    ['text' => 'г. Томск, ул. 79 Гвардейской Дивизии — нечётные дома 1–55', 'type' => 'Диапазон, нечётные'],
                    ['text' => 'г. Томск, ул. Интернационалистов — полностью', 'type' => 'Улица целиком'],
                ],
                'more' => 4,
                'transfer' => null,
            ],
            [
                'id' => 2,
                'name' => 'Участок №2 — Северный',
                'code' => '02',
                'description' => 'Городская территория и прилегающий посёлок',
                'doctor' => 'Петров Пётр Петрович',
                'speciality' => 'Врач-психиатр участковый',
                'initials' => 'ПП',
                'addresses' => [
                    ['text' => 'г. Томск, ул. Мичурина — дома 60–112', 'type' => 'Диапазон домов'],
                    ['text' => 'пос. Светлый — полностью', 'type' => 'Населённый пункт целиком'],
                    ['text' => 'г. Томск, пер. Новый, дом 7', 'type' => 'Конкретный адрес'],
                ],
                'more' => 1,
                'transfer' => [
                    'doctor' => 'Сидоров Алексей Николаевич',
                    'dates' => '21.09.2026 — 04.10.2026',
                    'reason' => 'Замещение на период отпуска основного врача',
                ],
            ],
            [
                'id' => 3,
                'name' => 'Участок №3 — Центральный',
                'code' => '03',
                'description' => 'Центральная часть города',
                'doctor' => 'Соколова Мария Андреевна',
                'speciality' => 'Врач-психиатр участковый',
                'initials' => 'СМ',
                'addresses' => [
                    ['text' => 'г. Томск, пр. Ленина — дома 100–151', 'type' => 'Диапазон домов'],
                    ['text' => 'г. Томск, ул. Советская — чётные дома 2–90', 'type' => 'Диапазон, чётные'],
                ],
                'more' => 0,
                'transfer' => null,
            ],
        ];
    }
}
