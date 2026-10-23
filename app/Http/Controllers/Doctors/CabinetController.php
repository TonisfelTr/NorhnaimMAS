<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Requests\ClinicOfficeStoreRequest;
use App\Http\Requests\VacationScheduleDeleteRequest;
use App\Http\Requests\VacationScheduleStoreRequest;
use App\Http\Requests\VacationScheduleUpdateRequest;
use App\Models\ClinicBuilding;
use App\Models\ClinicBuildingWorkSchedule;
use App\Models\ClinicCabinet;
use App\Models\ClinicCabinetSchedule;
use App\Models\ClinicDistrict;
use App\Models\ClinicScheduleException;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\EmployeeVacation;
use App\Models\EmployeeWorkSchedule;
use App\Models\Nurse;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CabinetController extends SettingsBaseController
{
    private function saveOffice(ClinicOfficeStoreRequest $request)
    {
        $clinic_id = auth()->user()->doctor->clinic_id;

        $inpatientDoctorIds = $this->inpatientDoctorIdsForClinic(
            (int) $clinic_id
        );
        $inpatientNurseIds = $this->inpatientNurseIdsForClinic(
            (int) $clinic_id
        );
        $inpatientNurseNames =
            $this->inpatientNurseNormalizedNamesForClinic(
                (int) $clinic_id
            );

        foreach ([
            'doctor' => $request->integer('doctor'),
            'replace_doctor_id' =>
                $request->integer('replace_doctor_id'),
        ] as $field => $doctorId) {
            if (
                $doctorId > 0
                && in_array($doctorId, $inpatientDoctorIds, true)
            ) {
                throw ValidationException::withMessages([
                    $field =>
                        'Этот врач работает в стационаре и не может быть назначен в амбулаторный кабинет.',
                ]);
            }
        }

        $nurseId = $request->integer('nurse');

        if (
            $nurseId > 0
            && in_array($nurseId, $inpatientNurseIds, true)
        ) {
            throw ValidationException::withMessages([
                'nurse' =>
                    'Эта медсестра работает в стационаре и не может быть назначена в амбулаторный кабинет.',
            ]);
        }

        $replaceNurseDoctorId =
            $request->integer('replace_nurse_id');

        if (
            $replaceNurseDoctorId > 0
            && $inpatientNurseNames !== []
        ) {
            $replaceNurseDoctor = Doctor::query()
                ->where('clinic_id', $clinic_id)
                ->find($replaceNurseDoctorId);

            if ($replaceNurseDoctor) {
                $normalized = mb_strtolower(
                    trim(
                        preg_replace(
                            '/\s+/u',
                            ' ',
                            implode(' ', array_filter([
                                $replaceNurseDoctor->surname,
                                $replaceNurseDoctor->name,
                                $replaceNurseDoctor->patronym,
                            ], static fn ($value): bool =>
                                $value !== null
                                && trim((string) $value) !== ''
                            ))
                        )
                    )
                );

                if (
                    in_array(
                        $normalized,
                        $inpatientNurseNames,
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'replace_nurse_id' =>
                            'Эта медсестра работает в стационаре и не может быть назначена на амбулаторное замещение.',
                    ]);
                }
            }
        }

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

    private function clinicScheduleDefinitions(): array
    {
        return [
            1 => [
                'key' => 'mon',
                'short' => 'ПН',
                'name' => 'Понедельник',
                'enabled' => true,
                'from' => '08:00',
                'to' => '20:00',
            ],
            2 => [
                'key' => 'tue',
                'short' => 'ВТ',
                'name' => 'Вторник',
                'enabled' => true,
                'from' => '08:00',
                'to' => '20:00',
            ],
            3 => [
                'key' => 'wed',
                'short' => 'СР',
                'name' => 'Среда',
                'enabled' => true,
                'from' => '08:00',
                'to' => '20:00',
            ],
            4 => [
                'key' => 'thu',
                'short' => 'ЧТ',
                'name' => 'Четверг',
                'enabled' => true,
                'from' => '08:00',
                'to' => '20:00',
            ],
            5 => [
                'key' => 'fri',
                'short' => 'ПТ',
                'name' => 'Пятница',
                'enabled' => true,
                'from' => '08:00',
                'to' => '20:00',
            ],
            6 => [
                'key' => 'sat',
                'short' => 'СБ',
                'name' => 'Суббота',
                'enabled' => true,
                'from' => '09:00',
                'to' => '15:00',
            ],
            7 => [
                'key' => 'sun',
                'short' => 'ВС',
                'name' => 'Воскресенье',
                'enabled' => false,
                'from' => '09:00',
                'to' => '15:00',
            ],
        ];
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

    /**
     * Врачи, постоянно относящиеся к стационару этой клиники.
     * Учитываем и заведующих, и обычных врачей отделений.
     */
    private function inpatientDoctorIdsForClinic(int $clinicId): array
    {
        if (!Schema::hasTable('hospital_departments')) {
            return [];
        }

        $ids = DB::table('hospital_departments')
            ->where('clinic_id', $clinicId)
            ->whereNotNull('head_doctor_id')
            ->pluck('head_doctor_id')
            ->map(static fn ($id): int => (int) $id);

        if (Schema::hasTable('hospital_department_doctors')) {
            $memberIds = DB::table('hospital_department_doctors as hdd')
                ->join(
                    'hospital_departments as hd',
                    'hd.id',
                    '=',
                    'hdd.hospital_department_id'
                )
                ->where('hd.clinic_id', $clinicId)
                ->pluck('hdd.doctor_id')
                ->map(static fn ($id): int => (int) $id);

            $ids = $ids->concat($memberIds);
        }

        return $ids
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Медсёстры, постоянно относящиеся к стационару этой клиники.
     */
    private function inpatientNurseIdsForClinic(int $clinicId): array
    {
        if (!Schema::hasTable('hospital_departments')) {
            return [];
        }

        return DB::table('hospital_departments')
            ->where('clinic_id', $clinicId)
            ->whereNotNull('head_nurse_id')
            ->pluck('head_nurse_id')
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function inpatientNurseNormalizedNamesForClinic(
        int $clinicId
    ): array {
        $nurseIds = $this->inpatientNurseIdsForClinic($clinicId);

        if ($nurseIds === []) {
            return [];
        }

        return Nurse::query()
            ->where('clinic_id', $clinicId)
            ->whereIn('id', $nurseIds)
            ->get([
                'surname',
                'name',
                'patronym',
            ])
            ->map(
                static fn (Nurse $nurse): string =>
                    mb_strtolower(
                        trim(
                            preg_replace(
                                '/\\s+/u',
                                ' ',
                                implode(' ', array_filter([
                                    $nurse->surname,
                                    $nurse->name,
                                    $nurse->patronym,
                                ], static fn ($value): bool =>
                                    $value !== null
                                    && trim((string) $value) !== ''
                                ))
                            )
                        )
                    )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function clinicDoctorsQuery(int $clinicId)
    {
        $inpatientDoctorIds = $this->inpatientDoctorIdsForClinic(
            $clinicId
        );

        return Doctor::query()
            ->where('clinic_id', $clinicId)
            ->when(
                $inpatientDoctorIds !== [],
                static fn ($query) =>
                    $query->whereNotIn('id', $inpatientDoctorIds)
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

    private function saveClinicScheduleException(
        Request $request,
        ?int $exceptionId = null
    ) {
        if (!Schema::hasTable('clinic_schedule_exceptions')) {
            return response()->json([
                'message' => 'Таблица особых периодов ещё не создана. Выполните php artisan migrate.',
            ], 503);
        }

        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        /*
         * Карантин всегда действует на всю клинику.
         * Клиент не может сузить его до врача/кабинета
         * или задать частичный интервал.
         */
        if ((string) $request->input('type') === 'quarantine') {
            $request->merge([
                'scope' => 'clinic',
                'mode' => 'closed',
                'all_day' => true,
                'time_from' => null,
                'time_to' => null,
                'target_ids' => [],
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'type' => [
                    'required',
                    Rule::in([
                        'quarantine',
                        'sanitary',
                        'technical',
                        'organizational',
                        'other',
                    ]),
                ],
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'date_from' => [
                    'required',
                    'date',
                ],
                'date_to' => [
                    'required',
                    'date',
                    'after_or_equal:date_from',
                ],
                'all_day' => [
                    'required',
                    'boolean',
                ],
                'time_from' => [
                    'nullable',
                    'date_format:H:i',
                ],
                'time_to' => [
                    'nullable',
                    'date_format:H:i',
                ],
                'mode' => [
                    'required',
                    Rule::in([
                        'closed',
                        'blocked',
                        'override',
                        'extra',
                    ]),
                ],
                'scope' => [
                    'required',
                    Rule::in([
                        'clinic',
                        'buildings',
                        'offices',
                        'doctors',
                    ]),
                ],
                'target_ids' => [
                    'nullable',
                    'array',
                    'max:100',
                ],
                'target_ids.*' => [
                    'integer',
                    'distinct',
                ],
                'comment' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'title.required' =>
                    'Укажите название ограничения.',
                'date_from.required' =>
                    'Укажите дату начала.',
                'date_to.required' =>
                    'Укажите дату окончания.',
                'date_to.after_or_equal' =>
                    'Дата окончания не может быть раньше даты начала.',
                'time_from.date_format' =>
                    'Время начала должно быть указано в формате ЧЧ:ММ.',
                'time_to.date_format' =>
                    'Время окончания должно быть указано в формате ЧЧ:ММ.',
            ]
        );

        $validator->after(
            function ($validator) use (
                $request,
                $clinicId
            ): void {
                $allDay = $request->boolean(
                    'all_day'
                );

                $timeFrom = trim(
                    (string) $request->input(
                        'time_from',
                        ''
                    )
                );

                $timeTo = trim(
                    (string) $request->input(
                        'time_to',
                        ''
                    )
                );

                if (!$allDay) {
                    if ($timeFrom === '') {
                        $validator
                            ->errors()
                            ->add(
                                'time_from',
                                'Укажите время начала ограничения.'
                            );
                    }

                    if ($timeTo === '') {
                        $validator
                            ->errors()
                            ->add(
                                'time_to',
                                'Укажите время окончания ограничения.'
                            );
                    }

                    if (
                        $timeFrom !== ''
                        && $timeTo !== ''
                        && $timeFrom >= $timeTo
                    ) {
                        $validator
                            ->errors()
                            ->add(
                                'time_to',
                                'Время окончания должно быть позже времени начала.'
                            );
                    }
                }

                $mode = (string) $request->input(
                    'mode',
                    'closed'
                );

                if (
                    in_array(
                        $mode,
                        [
                            'blocked',
                            'override',
                            'extra',
                        ],
                        true
                    )
                    && $allDay
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'all_day',
                            'Для выбранного режима укажите конкретный интервал времени.'
                        );
                }

                $type = (string) $request->input(
                    'type',
                    'other'
                );

                $allowedModes = match ($type) {
                    'quarantine' => [
                        'closed',
                    ],
                    'sanitary' => [
                        'blocked',
                        'override',
                    ],
                    'technical' => [
                        'blocked',
                        'closed',
                        'override',
                    ],
                    'organizational' => [
                        'override',
                        'blocked',
                        'extra',
                    ],
                    default => [
                        'closed',
                        'blocked',
                        'override',
                        'extra',
                    ],
                };

                if (
                    !in_array(
                        $mode,
                        $allowedModes,
                        true
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'mode',
                            'Выбранный режим не подходит для этого типа мероприятия.'
                        );
                }

                $scope = (string) $request->input(
                    'scope',
                    'clinic'
                );

                $targetIds = collect(
                    $request->input(
                        'target_ids',
                        []
                    )
                )
                    ->map(
                        static fn ($id): int =>
                            (int) $id
                    )
                    ->filter(
                        static fn (int $id): bool =>
                            $id > 0
                    )
                    ->unique()
                    ->values();

                if ($scope === 'clinic') {
                    return;
                }

                if ($targetIds->isEmpty()) {
                    $validator
                        ->errors()
                        ->add(
                            'target_ids',
                            'Выберите хотя бы один корпус, кабинет или одного врача.'
                        );

                    return;
                }

                if ($scope === 'buildings') {
                    $existingIds = ClinicBuilding::query()
                        ->where('clinic_id', $clinicId)
                        ->whereIn('id', $targetIds)
                        ->pluck('id')
                        ->map(
                            static fn ($id): int =>
                                (int) $id
                        );

                    if (
                        $existingIds->count()
                        !== $targetIds->count()
                    ) {
                        $validator
                            ->errors()
                            ->add(
                                'target_ids',
                                'Один из выбранных корпусов не относится к текущей клинике.'
                            );
                    }

                    return;
                }

                if ($scope === 'offices') {
                    $existingIds =
                        ClinicCabinet::query()
                            ->where(
                                'clinic_id',
                                $clinicId
                            )
                            ->whereIn(
                                'id',
                                $targetIds
                            )
                            ->pluck('id')
                            ->map(
                                static fn ($id): int =>
                                    (int) $id
                            );

                    if (
                        $existingIds->count()
                        !== $targetIds->count()
                    ) {
                        $validator
                            ->errors()
                            ->add(
                                'target_ids',
                                'Один из выбранных кабинетов не относится к текущей клинике.'
                            );
                    }

                    return;
                }

                $existingIds = Doctor::query()
                    ->where(
                        'clinic_id',
                        $clinicId
                    )
                    ->whereIn(
                        'id',
                        $targetIds
                    )
                    ->pluck('id')
                    ->map(
                        static fn ($id): int =>
                            (int) $id
                    );

                if (
                    $existingIds->count()
                    !== $targetIds->count()
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'target_ids',
                            'Один из выбранных врачей не относится к текущей клинике.'
                        );
                }
            }
        );

        $input = $validator->validate();

        $targetIds = $input['scope']
            === 'clinic'
                ? []
                : collect(
                    $input['target_ids']
                    ?? []
                )
                    ->map(
                        static fn ($id): int =>
                            (int) $id
                    )
                    ->filter(
                        static fn (int $id): bool =>
                            $id > 0
                    )
                    ->unique()
                    ->values()
                    ->all();

        $rule = DB::transaction(
            function () use (
                $clinicId,
                $exceptionId,
                $input,
                $targetIds
            ): ClinicScheduleException {
                $rule = $exceptionId
                    ? ClinicScheduleException::query()
                        ->where(
                            'clinic_id',
                            $clinicId
                        )
                        ->findOrFail(
                            $exceptionId
                        )
                    : new ClinicScheduleException();

                $rule->fill([
                    'clinic_id' => $clinicId,
                    'type' => $input['type'],
                    'title' => trim(
                        (string) $input['title']
                    ),
                    'date_from' =>
                        $input['date_from'],
                    'date_to' =>
                        $input['date_to'],
                    'all_day' =>
                        (bool) $input['all_day'],
                    'time_from' =>
                        (bool) $input['all_day']
                            ? null
                            : $input['time_from'],
                    'time_to' =>
                        (bool) $input['all_day']
                            ? null
                            : $input['time_to'],
                    'mode' => $input['mode'],
                    'scope' => $input['scope'],
                    'target_ids' => $targetIds,
                    'comment' => trim(
                        (string) (
                            $input['comment']
                            ?? ''
                        )
                    ) ?: null,
                ]);

                $rule->save();

                return $rule;
            }
        );

        return response()->json([
            'ok' => true,
            'id' => (int) $rule->id,
            'message' => $exceptionId
                ? 'Ограничение обновлено.'
                : 'Ограничение добавлено.',
        ]);
    }

    private function presentClinicScheduleException(
        ClinicScheduleException $exception,
        array $clinicOffices,
        array $clinicDoctors,
        array $buildingTabs
    ): array {
        $isQuarantine = (string) $exception->type === 'quarantine';

        $targetIds = (
            $isQuarantine
                ? collect()
                : collect($exception->target_ids ?? [])
        )
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        $buildingLabels = collect($buildingTabs)
            ->mapWithKeys(
                static fn (array $building): array => [
                    (string) $building['key'] => (string) $building['label'],
                ]
            );

        $scopeText = 'Вся клиника';
        $buildingKeys = collect(['all']);

        if (!$isQuarantine && $exception->scope === 'buildings') {
            $buildingKeys = $targetIds
                ->map(static fn (int $id): string => (string) $id)
                ->filter(
                    static fn (string $key): bool =>
                        $key !== ''
                )
                ->unique()
                ->values();

            $scopeText = $buildingKeys->count() === 1
                ? 'Весь корпус'
                : 'Выбранные корпуса';
        } elseif (!$isQuarantine && $exception->scope === 'offices') {
            $targets = collect($clinicOffices)
                ->whereIn('id', $targetIds)
                ->values();

            $scopeText = $targets
                ->pluck('label')
                ->implode(', ');

            $buildingKeys = $targets
                ->pluck('building_key')
                ->filter()
                ->map(static fn ($key): string => (string) $key)
                ->unique()
                ->values();
        } elseif (
            !$isQuarantine
            && $exception->scope === 'doctors'
        ) {
            $targets = collect($clinicDoctors)
                ->whereIn('id', $targetIds)
                ->values();

            $scopeText = $targets
                ->pluck('label')
                ->implode(', ');

            $buildingKeys = $targets
                ->pluck('building_keys')
                ->flatten()
                ->filter()
                ->map(static fn ($key): string => (string) $key)
                ->unique()
                ->values();
        }

        if ($scopeText === '') {
            $scopeText = 'Выбранные объекты не найдены';
        }

        if ($buildingKeys->isEmpty()) {
            $buildingKeys = collect(['all']);
        }

        $buildingText = $buildingKeys->contains('all')
            ? 'Все корпуса'
            : $buildingKeys
                ->map(
                    static fn (string $key): string =>
                        (string) ($buildingLabels->get($key) ?: 'Корпус #' . $key)
                )
                ->unique()
                ->implode(', ');

        $normalizedMode = $isQuarantine
            ? 'closed'
            : match ($exception->mode) {
                'shortened' => 'override',
                'limited' => 'blocked',
                default => (string) $exception->mode,
            };

        $modeMeta = match ($normalizedMode) {
            'override' => [
                'badge' => 'Особый график',
                'badge_class' => 'clinic-badge--info',
                'card_class' => 'clinic-exception--info',
            ],
            'blocked' => [
                'badge' => 'Приём приостановлен',
                'badge_class' => 'clinic-badge--warning',
                'card_class' => 'clinic-exception--warning',
            ],
            'extra' => [
                'badge' => 'Дополнительный приём',
                'badge_class' => 'clinic-badge--success',
                'card_class' => 'clinic-exception--success',
            ],
            default => [
                'badge' => 'Приём закрыт',
                'badge_class' => 'clinic-badge--danger',
                'card_class' => 'clinic-exception--danger',
            ],
        };

        $dateFrom = Carbon::parse($exception->date_from);
        $dateTo = Carbon::parse($exception->date_to);

        $dateText = $dateFrom->isSameDay($dateTo)
            ? $dateFrom->format('d.m.Y')
            : $dateFrom->format('d.m.Y')
                . ' — '
                . $dateTo->format('d.m.Y');

        $timeText = $exception->all_day
            ? 'Весь день'
            : substr((string) $exception->time_from, 0, 5)
                . ' — '
                . substr((string) $exception->time_to, 0, 5);

        return [
            'id' => (int) $exception->id,
            'date_text' => $dateText,
            'time_text' => $timeText,
            'title' => $exception->title,
            'description' => $exception->comment
                ?: 'Временное изменение режима работы.',
            'scope_text' => $scopeText,
            'building_text' => $buildingText,
            'building_keys' => $buildingKeys->all(),
            'badge' => $modeMeta['badge'],
            'badge_class' => $modeMeta['badge_class'],
            'card_class' => $modeMeta['card_class'],
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'form' => [
                'id' => (int) $exception->id,
                'type' => $exception->type,
                'title' => $exception->title,
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'all_day' => $isQuarantine
                    ? true
                    : (bool) $exception->all_day,
                'time_from' => $isQuarantine
                    ? null
                    : ($exception->time_from
                        ? substr((string) $exception->time_from, 0, 5)
                        : null),
                'time_to' => $isQuarantine
                    ? null
                    : ($exception->time_to
                        ? substr((string) $exception->time_to, 0, 5)
                        : null),
                'mode' => $normalizedMode,
                'scope' => $isQuarantine
                    ? 'clinic'
                    : $exception->scope,
                'target_ids' => $isQuarantine
                    ? []
                    : $targetIds->all(),
                'comment' => $exception->comment ?? '',
            ],
        ];
    }

    private function ensureVacationDoesNotOverlap(
        int $clinicId,
        string $employeeType,
        int $employeeId,
        string $dateFrom,
        string $dateTo,
        ?int $ignoreVacationId = null
    ): void {
        $overlap = EmployeeVacation::query()
            ->where('clinic_id', $clinicId)
            ->where('employee_type', $employeeType)
            ->where('employee_id', $employeeId)
            ->when(
                $ignoreVacationId,
                static fn ($query) => $query->where('id', '<>', $ignoreVacationId)
            )
            ->whereDate('date_from', '<=', $dateTo)
            ->whereDate('date_to', '>=', $dateFrom)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'date_from' => 'У сотрудника уже есть отпуск, пересекающийся с выбранным периодом.',
            ]);
        }
    }

    private function ensureVacationReplacementAvailable(
        int $clinicId,
        string $employeeType,
        ?int $replacementEmployeeId,
        string $dateFrom,
        string $dateTo,
        ?int $ignoreVacationId = null
    ): void {
        if (!$replacementEmployeeId) {
            return;
        }

        $replacementOnVacation = EmployeeVacation::query()
            ->where('clinic_id', $clinicId)
            ->where('employee_type', $employeeType)
            ->where('employee_id', $replacementEmployeeId)
            ->where('status', 'approved')
            ->when(
                $ignoreVacationId,
                static fn ($query) =>
                    $query->where('id', '<>', $ignoreVacationId)
            )
            ->whereDate('date_from', '<=', $dateTo)
            ->whereDate('date_to', '>=', $dateFrom)
            ->exists();

        if (!$replacementOnVacation) {
            return;
        }

        $field = $employeeType === 'nurse'
            ? 'replace_nurse_id'
            : 'replace_doctor_id';

        throw ValidationException::withMessages([
            $field => $employeeType === 'nurse'
                ? 'Выбранная исполняющая обязанности медсестра сама находится в утверждённом отпуске в этот период.'
                : 'Выбранный исполняющий обязанности врач сам находится в утверждённом отпуске в этот период.',
        ]);
    }

    private function vacationEmployeesForClinic(int $clinicId)
    {
        $doctorRows = Doctor::query()
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
                            ]
                        );
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $doctorEmployees = $doctorRows->map(
            function (Doctor $doctor): array {
                $fullName = trim(implode(' ', array_filter([
                    $doctor->surname ?? null,
                    $doctor->name ?? null,
                    $doctor->patronym ?? null,
                ])));

                $profession = trim((string) ($doctor->profession ?? ''));
                $status = $this->cleanDoctorSpeciality(
                    (string) ($doctor->status ?? '')
                );

                if ($profession === 'Медицинский регистратор') {
                    $role = $profession;
                    $category = 'registrar';
                } else {
                    $role = $status !== ''
                        ? $status
                        : ($profession !== '' ? $profession : 'Врач');
                    $category = 'doctor';
                }

                $initials = mb_substr((string) ($doctor->surname ?? ''), 0, 1)
                    . mb_substr((string) ($doctor->name ?? ''), 0, 1);

                return [
                    'key' => 'doctor:' . $doctor->id,
                    'type' => 'doctor',
                    'id' => (int) $doctor->id,
                    'name' => $fullName !== '' ? $fullName : 'Сотрудник #' . $doctor->id,
                    'role' => $role,
                    'category' => $category,
                    'initials' => mb_strtoupper($initials),
                ];
            }
        );

        $nurseEmployees = Nurse::query()
            ->where('clinic_id', $clinicId)
            ->orderBy('surname')
            ->orderBy('name')
            ->get()
            ->map(
                static function (Nurse $nurse): array {
                    $fullName = trim(implode(' ', array_filter([
                        $nurse->surname ?? null,
                        $nurse->name ?? null,
                        $nurse->patronym ?? null,
                    ])));

                    $initials = mb_substr((string) ($nurse->surname ?? ''), 0, 1)
                        . mb_substr((string) ($nurse->name ?? ''), 0, 1);

                    return [
                        'key' => 'nurse:' . $nurse->id,
                        'type' => 'nurse',
                        'id' => (int) $nurse->id,
                        'name' => $fullName !== '' ? $fullName : 'Медсестра #' . $nurse->id,
                        'role' => trim((string) ($nurse->profession ?? '')) ?: 'Медицинская сестра',
                        'category' => 'nurse',
                        'initials' => mb_strtoupper($initials),
                    ];
                }
            );

        return $doctorEmployees
            ->concat($nurseEmployees)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
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
        $inpatientNurseIds = $this->inpatientNurseIdsForClinic(
            (int) $clinic_id
        );

        $nurses = Nurse::where('clinic_id', $clinic_id)
            ->when(
                $inpatientNurseIds !== [],
                static fn ($query) =>
                    $query->whereNotIn('id', $inpatientNurseIds)
            )
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
        $inpatientNurseNames =
            $this->inpatientNurseNormalizedNamesForClinic(
                (int) $clinic_id
            );

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
            ->get()
            ->reject(
                static function (Doctor $nurseDoctor) use (
                    $inpatientNurseNames
                ): bool {
                    $normalized = mb_strtolower(
                        trim(
                            preg_replace(
                                '/\s+/u',
                                ' ',
                                implode(' ', array_filter([
                                    $nurseDoctor->surname,
                                    $nurseDoctor->name,
                                    $nurseDoctor->patronym,
                                ], static fn ($value): bool =>
                                    $value !== null
                                    && trim((string) $value) !== ''
                                ))
                            )
                        )
                    );

                    return in_array(
                        $normalized,
                        $inpatientNurseNames,
                        true
                    );
                }
            )
            ->values();

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
            ->with([
                'building:id,number,name',
            ])
            ->get([
                'id',
                'building_id',
                'cabinet',
                'name',
                'stage',
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
         * Отпуск сотрудника хранится отдельно от постоянного замещения
         * кабинета. Поэтому состояние кабинета на текущую дату собираем
         * динамически из employee_vacations.
         *
         * clinic_cabinets.replace_* здесь не изменяем: у этих полей нет
         * периода действия, а отпуск ограничен date_from/date_to.
         */
        $today = Carbon::today();
        $activeVacations = collect();

        if (Schema::hasTable('employee_vacations')) {
            $activeVacations = EmployeeVacation::query()
                ->where('clinic_id', $clinic_id)
                ->where('status', 'approved')
                ->whereDate('date_from', '<=', $today->toDateString())
                ->whereDate('date_to', '>=', $today->toDateString())
                ->with([
                    'replaceDoctor:id,clinic_id,name,surname,patronym,status,profession',
                    'replaceNurse:id,clinic_id,name,surname,patronym,profession',
                ])
                ->orderBy('date_from')
                ->get();
        }

        $doctorVacations = $activeVacations
            ->where('employee_type', 'doctor')
            ->keyBy(
                static fn (EmployeeVacation $vacation): int =>
                    (int) $vacation->employee_id
            );

        $nurseVacations = $activeVacations
            ->where('employee_type', 'nurse')
            ->keyBy(
                static fn (EmployeeVacation $vacation): int =>
                    (int) $vacation->employee_id
            );

        $vacationDoctors = Doctor::query()
            ->where('clinic_id', $clinic_id)
            ->whereIn(
                'id',
                $doctorVacations->keys()->all()
            )
            ->get([
                'id',
                'name',
                'surname',
                'patronym',
                'status',
                'profession',
            ])
            ->keyBy('id');

        $vacationNurses = Nurse::query()
            ->where('clinic_id', $clinic_id)
            ->whereIn(
                'id',
                $nurseVacations->keys()->all()
            )
            ->get([
                'id',
                'name',
                'surname',
                'patronym',
                'profession',
            ])
            ->keyBy('id');

        $officeLocation = static function (ClinicCabinet $cabinet): array {
            return [
                'id' => (int) $cabinet->id,
                'cabinet' => (string) $cabinet->cabinet,
                'name' => (string) ($cabinet->name ?? ''),
                'stage' => $cabinet->stage,
                'cabinet_type' => (string) $cabinet->cabinet_type,
                'building_id' => $cabinet->building_id
                    ? (int) $cabinet->building_id
                    : null,
                'building_number' => (string) (
                    $cabinet->building?->number
                    ?? ''
                ),
                'building_name' => (string) (
                    $cabinet->building?->name
                    ?? ''
                ),
            ];
        };

        $doctorOfficeLocations = $staffAssignments
            ->whereNotNull('doctor_id')
            ->groupBy(
                static fn (ClinicCabinet $cabinet): int =>
                    (int) $cabinet->doctor_id
            )
            ->map(
                static fn ($items): array =>
                    $items
                        ->map($officeLocation)
                        ->values()
                        ->all()
            );

        $nurseOfficeLocations = $staffAssignments
            ->whereNotNull('nurse_id')
            ->groupBy(
                static fn (ClinicCabinet $cabinet): int =>
                    (int) $cabinet->nurse_id
            )
            ->map(
                static fn ($items): array =>
                    $items
                        ->map($officeLocation)
                        ->values()
                        ->all()
            );

        $vacationPeriod = static function (EmployeeVacation $vacation): string {
            $from = $vacation->date_from?->format('d.m.Y') ?: '—';
            $to = $vacation->date_to?->format('d.m.Y') ?: '—';

            return $from === $to
                ? $from
                : $from . ' — ' . $to;
        };

        $cabinets->getCollection()->each(
            function (ClinicCabinet $cabinet) use (
                $activeVacations,
                $doctorVacations,
                $nurseVacations,
                $vacationDoctors,
                $vacationNurses,
                $doctorOfficeLocations,
                $nurseOfficeLocations,
                $vacationPeriod
            ): void {
                $cabinet->doctor_vacation = null;
                $cabinet->nurse_vacation = null;
                $cabinet->doctor_vacation_duties = [];
                $cabinet->nurse_vacation_duties = [];

                if ($cabinet->doctor_id) {
                    $doctorVacation = $doctorVacations->get(
                        (int) $cabinet->doctor_id
                    );

                    if ($doctorVacation) {
                        $replaceDoctor = $doctorVacation->replaceDoctor;

                        $cabinet->doctor_vacation = [
                            'id' => (int) $doctorVacation->id,
                            'period' => $vacationPeriod($doctorVacation),
                            'date_from' => $doctorVacation->date_from?->format('Y-m-d'),
                            'date_to' => $doctorVacation->date_to?->format('Y-m-d'),
                            'replace_id' => $doctorVacation->replace_doctor_id
                                ? (int) $doctorVacation->replace_doctor_id
                                : null,
                            'replace_name' => $replaceDoctor?->full_name ?: '',
                            'replace_role' => trim(
                                (string) (
                                    $replaceDoctor?->status
                                    ?: $replaceDoctor?->profession
                                    ?: ''
                                )
                            ),
                            'replace_locations' => $doctorVacation->replace_doctor_id
                                ? $doctorOfficeLocations->get(
                                    (int) $doctorVacation->replace_doctor_id,
                                    []
                                )
                                : [],
                        ];
                    }

                    $cabinet->doctor_vacation_duties = $activeVacations
                        ->filter(
                            static fn (EmployeeVacation $vacation): bool =>
                                $vacation->employee_type === 'doctor'
                                && (int) ($vacation->replace_doctor_id ?? 0)
                                    === (int) $cabinet->doctor_id
                        )
                        ->map(
                            function (EmployeeVacation $vacation) use (
                                $vacationDoctors,
                                $doctorOfficeLocations,
                                $vacationPeriod
                            ): array {
                                $absentDoctor = $vacationDoctors->get(
                                    (int) $vacation->employee_id
                                );

                                return [
                                    'vacation_id' => (int) $vacation->id,
                                    'employee_id' => (int) $vacation->employee_id,
                                    'employee_name' => $absentDoctor?->full_name
                                        ?: 'Врач #' . $vacation->employee_id,
                                    'period' => $vacationPeriod($vacation),
                                    'employee_locations' => $doctorOfficeLocations->get(
                                        (int) $vacation->employee_id,
                                        []
                                    ),
                                ];
                            }
                        )
                        ->values()
                        ->all();
                }

                if ($cabinet->nurse_id) {
                    $nurseVacation = $nurseVacations->get(
                        (int) $cabinet->nurse_id
                    );

                    if ($nurseVacation) {
                        $replaceNurse = $nurseVacation->replaceNurse;

                        $cabinet->nurse_vacation = [
                            'id' => (int) $nurseVacation->id,
                            'period' => $vacationPeriod($nurseVacation),
                            'date_from' => $nurseVacation->date_from?->format('Y-m-d'),
                            'date_to' => $nurseVacation->date_to?->format('Y-m-d'),
                            'replace_id' => $nurseVacation->replace_nurse_id
                                ? (int) $nurseVacation->replace_nurse_id
                                : null,
                            'replace_name' => $replaceNurse?->full_name ?: '',
                            'replace_role' => trim(
                                (string) (
                                    $replaceNurse?->profession
                                    ?: 'Медицинская сестра'
                                )
                            ),
                            'replace_locations' => $nurseVacation->replace_nurse_id
                                ? $nurseOfficeLocations->get(
                                    (int) $nurseVacation->replace_nurse_id,
                                    []
                                )
                                : [],
                        ];
                    }

                    $cabinet->nurse_vacation_duties = $activeVacations
                        ->filter(
                            static fn (EmployeeVacation $vacation): bool =>
                                $vacation->employee_type === 'nurse'
                                && (int) ($vacation->replace_nurse_id ?? 0)
                                    === (int) $cabinet->nurse_id
                        )
                        ->map(
                            function (EmployeeVacation $vacation) use (
                                $vacationNurses,
                                $nurseOfficeLocations,
                                $vacationPeriod
                            ): array {
                                $absentNurse = $vacationNurses->get(
                                    (int) $vacation->employee_id
                                );

                                return [
                                    'vacation_id' => (int) $vacation->id,
                                    'employee_id' => (int) $vacation->employee_id,
                                    'employee_name' => $absentNurse?->full_name
                                        ?: 'Медсестра #' . $vacation->employee_id,
                                    'period' => $vacationPeriod($vacation),
                                    'employee_locations' => $nurseOfficeLocations->get(
                                        (int) $vacation->employee_id,
                                        []
                                    ),
                                ];
                            }
                        )
                        ->values()
                        ->all();
                }
            }
        );

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

        return $this->settingsView('doctors.settings.offices', compact(
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
        /*
         * Сам маршрут уже однозначно означает создание кабинета.
         * Не доверяем скрытому полю mode из модалки: оно может остаться
         * от предыдущего редактирования.
         */
        $request->merge([
            'mode' => 'create',
        ]);

        return $this->saveOffice($request);
    }

    public function clinicDoctorUpdate(
        ClinicOfficeStoreRequest $request
    ) {
        /*
         * Аналогично, маршрут update всегда означает редактирование.
         * Принудительно синхронизируем mode с вызванным endpoint.
         */
        $request->merge([
            'mode' => 'edit',
        ]);

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

        return $this->settingsView(
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

    public function indexDoctorScheduleTable()
    {
        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $definitions = $this->clinicScheduleDefinitions();

        $clinicScheduleRows = ClinicWorkShedule::query()
            ->where('clinic_id', $clinicId)
            ->get()
            ->keyBy('day_number');

        $clinicSchedule = collect($definitions)
            ->mapWithKeys(
                static function (
                    array $definition,
                    int $dayNumber
                ) use ($clinicScheduleRows): array {
                    $row = $clinicScheduleRows->get($dayNumber);

                    return [
                        $dayNumber - 1 => [
                            'enabled' => $row
                                ? (bool) $row->enabled
                                : (bool) $definition['enabled'],

                            'from' => $row && $row->start_time
                                ? substr(
                                    (string) $row->start_time,
                                    0,
                                    5
                                )
                                : $definition['from'],

                            'to' => $row && $row->end_time
                                ? substr(
                                    (string) $row->end_time,
                                    0,
                                    5
                                )
                                : $definition['to'],
                        ],
                    ];
                }
            )
            ->all();

        $buildingScheduleRows = Schema::hasTable(
            'clinic_building_work_schedules'
        )
            ? ClinicBuildingWorkSchedule::query()
                ->where('clinic_id', $clinicId)
                ->get()
                ->groupBy('building_id')
                ->map(
                    static fn ($rows) =>
                        $rows->keyBy('day_number')
                )
            : collect();

        /*
         * Расписание врачей строим только по активным кабинетам,
         * где назначен основной врач.
         *
         * Источник времени тот же, что и в карточке кабинета:
         * employee_work_schedules для employee_type = doctor.
         *
         * Если расписание сотрудника не задано, кабинет в календарь
         * вообще ничего не добавляет.
         */
        $cabinets = ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->whereNotNull('doctor_id')
            ->with([
                'mainDoctor:id,name,surname,patronym,status,profession',
            ])
            ->orderByRaw(
                "NULLIF(substring(cabinet from '^[0-9]+'), '')::integer ASC NULLS LAST"
            )
            ->orderBy('cabinet')
            ->get([
                'id',
                'building_id',
                'doctor_id',
                'cabinet',
                'name',
            ]);

        $doctorIds = $cabinets
            ->pluck('doctor_id')
            ->filter()
            ->unique()
            ->values();

        $scheduleRows = $doctorIds->isEmpty()
            ? collect()
            : EmployeeWorkSchedule::query()
                ->where('clinic_id', $clinicId)
                ->where('employee_type', 'doctor')
                ->whereIn('employee_id', $doctorIds)
                ->where('enabled', true)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->orderBy('day_number')
                ->orderBy('start_time')
                ->get()
                ->groupBy('employee_id');

        $doctorShifts = collect();
        $shiftId = 1;

        foreach ($cabinets as $cabinet) {
            $doctor = $cabinet->mainDoctor;

            if (!$doctor) {
                continue;
            }

            $doctorSchedule = $scheduleRows->get(
                (int) $cabinet->doctor_id,
                collect()
            );

            /*
             * Точное соответствие карточке кабинета:
             * «Расписание сотрудника не задано» = календарная запись
             * для этого кабинета не создаётся.
             */
            if ($doctorSchedule->isEmpty()) {
                continue;
            }

            $doctorName = $doctor->full_name
                ?: 'Врач #' . $doctor->id;

            $speciality = $this->cleanDoctorSpeciality(
                (string) ($doctor->status ?? '')
            );

            if ($speciality === '') {
                $speciality = trim(
                    (string) ($doctor->profession ?: 'Врач')
                );
            }

            $office = trim((string) $cabinet->cabinet);

            if ($office === '') {
                $office = '#' . $cabinet->id;
            }

            foreach ($doctorSchedule as $schedule) {
                $dayNumber = (int) $schedule->day_number;

                if ($dayNumber < 1 || $dayNumber > 7) {
                    continue;
                }

                $buildingRows = $cabinet->building_id
                    ? $buildingScheduleRows->get(
                        (int) $cabinet->building_id,
                        collect()
                    )
                    : collect();

                $scheduleOwnerRow = $buildingRows->get($dayNumber)
                    ?? $clinicScheduleRows->get($dayNumber);

                $definition = $definitions[$dayNumber] ?? null;

                $clinicDay = [
                    'enabled' => $scheduleOwnerRow
                        ? (bool) $scheduleOwnerRow->enabled
                        : (bool) ($definition['enabled'] ?? false),
                    'from' => $scheduleOwnerRow
                        && $scheduleOwnerRow->start_time
                            ? substr((string) $scheduleOwnerRow->start_time, 0, 5)
                            : (string) ($definition['from'] ?? ''),
                    'to' => $scheduleOwnerRow
                        && $scheduleOwnerRow->end_time
                            ? substr((string) $scheduleOwnerRow->end_time, 0, 5)
                            : (string) ($definition['to'] ?? ''),
                ];

                if (empty($clinicDay['enabled'])) {
                    continue;
                }

                $start = substr(
                    (string) $schedule->start_time,
                    0,
                    5
                );

                $end = substr(
                    (string) $schedule->end_time,
                    0,
                    5
                );

                if (
                    $start === ''
                    || $end === ''
                    || $start >= $end
                ) {
                    continue;
                }

                /*
                 * Не выводим старые некорректные записи за пределами
                 * рабочего времени клиники.
                 */
                if (
                    $start < $clinicDay['from']
                    || $end > $clinicDay['to']
                ) {
                    continue;
                }

                $doctorShifts->push([
                    'id' => $shiftId++,
                    'schedule_id' => (int) $schedule->id,
                    'day' => $dayNumber - 1,
                    'doctor_id' => (int) $doctor->id,
                    'doctor' => $doctorName,
                    'speciality' => $speciality,
                    'office_id' => (int) $cabinet->id,
                    'office' => $office,
                    'start' => $start,
                    'end' => $end,
                ]);
            }
        }

        $doctorOptions = $doctorShifts
            ->unique('doctor_id')
            ->sortBy('doctor', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(
                static fn (array $shift): array => [
                    'id' => $shift['doctor_id'],
                    'name' => $shift['doctor'],
                    'speciality' => $shift['speciality'],
                    'office' => $shift['office'],
                ]
            )
            ->values()
            ->all();

        $specialityOptions = $doctorShifts
            ->pluck('speciality')
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $officeOptions = $doctorShifts
            ->unique('office_id')
            ->sortBy('office', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(
                static fn (array $shift): array => [
                    'id' => $shift['office_id'],
                    'number' => $shift['office'],
                ]
            )
            ->values()
            ->all();

        return $this->settingsView(
            'doctors.settings.doctors_schedule',
            [
                'clinicSchedule' => $clinicSchedule,
                'doctorShifts' => $doctorShifts->values()->all(),
                'doctorOptions' => $doctorOptions,
                'specialityOptions' => $specialityOptions,
                'officeOptions' => $officeOptions,
            ]
        );
    }

    public function updateDoctorScheduleShift(
        Request $request
    ) {
        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $data = Validator::make(
            $request->all(),
            [
                'action' => [
                    'nullable',
                    Rule::in([
                        'save',
                        'delete',
                    ]),
                ],
                'schedule_id' => [
                    'nullable',
                    'integer',
                ],
                'doctor_id' => [
                    'nullable',
                    'integer',
                ],
                'day' => [
                    'nullable',
                    'integer',
                    'between:0,6',
                ],
                'start' => [
                    'nullable',
                    'date_format:H:i',
                ],
                'end' => [
                    'nullable',
                    'date_format:H:i',
                ],
            ],
            [
                'day.between' =>
                    'Некорректный день недели.',
                'start.date_format' =>
                    'Некорректное время начала.',
                'end.date_format' =>
                    'Некорректное время окончания.',
            ]
        )->validate();

        $action = (string) (
            $data['action']
            ?? 'save'
        );

        $scheduleId = (int) (
            $data['schedule_id']
            ?? 0
        );

        if ($action === 'delete') {
            if ($scheduleId <= 0) {
                throw ValidationException::withMessages([
                    'schedule_id' =>
                        'Не удалось определить смену.',
                ]);
            }

            DB::transaction(
                function () use (
                    $clinicId,
                    $scheduleId
                ): void {
                    $schedule =
                        EmployeeWorkSchedule::query()
                            ->where('id', $scheduleId)
                            ->where(
                                'clinic_id',
                                $clinicId
                            )
                            ->where(
                                'employee_type',
                                'doctor'
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    $schedule->enabled = false;
                    $schedule->start_time = null;
                    $schedule->end_time = null;
                    $schedule->save();
                }
            );

            return response()->json([
                'ok' => true,
                'deleted' => true,
            ]);
        }

        if (
            !array_key_exists('day', $data)
            || empty($data['start'])
            || empty($data['end'])
        ) {
            throw ValidationException::withMessages([
                'schedule' =>
                    'Укажите день, время начала и окончания смены.',
            ]);
        }

        $dayIndex = (int) $data['day'];
        $dayNumber = $dayIndex + 1;
        $start = (string) $data['start'];
        $end = (string) $data['end'];

        [$startHour, $startMinute] = array_map(
            'intval',
            explode(':', $start)
        );

        [$endHour, $endMinute] = array_map(
            'intval',
            explode(':', $end)
        );

        $startMinutes =
            $startHour * 60
            + $startMinute;

        $endMinutes =
            $endHour * 60
            + $endMinute;

        if (
            $startMinutes % 15 !== 0
            || $endMinutes % 15 !== 0
        ) {
            throw ValidationException::withMessages([
                'start' =>
                    'Время должно изменяться с шагом 15 минут.',
            ]);
        }

        if ($startMinutes >= $endMinutes) {
            throw ValidationException::withMessages([
                'end' =>
                    'Время окончания должно быть позже времени начала.',
            ]);
        }

        $saved = DB::transaction(
            function () use (
                $clinicId,
                $scheduleId,
                $data,
                $dayNumber,
                $start,
                $end
            ): EmployeeWorkSchedule {
                $source = null;

                if ($scheduleId > 0) {
                    $source =
                        EmployeeWorkSchedule::query()
                            ->where('id', $scheduleId)
                            ->where(
                                'clinic_id',
                                $clinicId
                            )
                            ->where(
                                'employee_type',
                                'doctor'
                            )
                            ->lockForUpdate()
                            ->firstOrFail();
                }

                $doctorId = $source
                    ? (int) $source->employee_id
                    : (int) (
                        $data['doctor_id']
                        ?? 0
                    );

                if ($doctorId <= 0) {
                    throw ValidationException::withMessages([
                        'doctor_id' =>
                            'Не удалось определить врача.',
                    ]);
                }

                $doctorCabinet =
                    ClinicCabinet::query()
                        ->where('clinic_id', $clinicId)
                        ->where('enabled', true)
                        ->where('doctor_id', $doctorId)
                        ->first([
                            'id',
                            'building_id',
                        ]);

                if (!$doctorCabinet) {
                    throw ValidationException::withMessages([
                        'doctor_id' =>
                            'Врач не закреплён за активным кабинетом.',
                    ]);
                }

                $definition = $this->clinicScheduleDefinitions()[$dayNumber]
                    ?? null;

                $clinicDay = null;

                if (
                    $doctorCabinet->building_id
                    && Schema::hasTable('clinic_building_work_schedules')
                ) {
                    $clinicDay = ClinicBuildingWorkSchedule::query()
                        ->where('clinic_id', $clinicId)
                        ->where('building_id', (int) $doctorCabinet->building_id)
                        ->where('day_number', $dayNumber)
                        ->first();
                }

                if (!$clinicDay) {
                    $clinicDay = ClinicWorkShedule::query()
                        ->where('clinic_id', $clinicId)
                        ->where('day_number', $dayNumber)
                        ->first();
                }

                $clinicEnabled = $clinicDay
                    ? (bool) $clinicDay->enabled
                    : (bool) ($definition['enabled'] ?? false);

                $clinicFrom = $clinicDay && $clinicDay->start_time
                    ? substr((string) $clinicDay->start_time, 0, 5)
                    : (string) ($definition['from'] ?? '');

                $clinicTo = $clinicDay && $clinicDay->end_time
                    ? substr((string) $clinicDay->end_time, 0, 5)
                    : (string) ($definition['to'] ?? '');

                if (!$clinicEnabled) {
                    throw ValidationException::withMessages([
                        'day' => 'В выбранный день корпус врача не работает.',
                    ]);
                }

                if (
                    $clinicFrom === ''
                    || $clinicTo === ''
                    || $start < $clinicFrom
                    || $end > $clinicTo
                ) {
                    throw ValidationException::withMessages([
                        'start' =>
                            'Смена должна находиться в пределах работы корпуса: '
                            . $clinicFrom
                            . '–'
                            . $clinicTo
                            . '.',
                    ]);
                }

                /*
                 * На один день у врача должна быть только одна
                 * строка расписания.
                 */
                $target =
                    EmployeeWorkSchedule::query()
                        ->where(
                            'clinic_id',
                            $clinicId
                        )
                        ->where(
                            'employee_type',
                            'doctor'
                        )
                        ->where(
                            'employee_id',
                            $doctorId
                        )
                        ->where(
                            'day_number',
                            $dayNumber
                        )
                        ->when(
                            $source,
                            static fn ($query) =>
                                $query->where(
                                    'id',
                                    '!=',
                                    $source->id
                                )
                        )
                        ->lockForUpdate()
                        ->first();

                if (
                    $target
                    && (bool) $target->enabled
                ) {
                    throw ValidationException::withMessages([
                        'day' =>
                            'У этого врача уже есть смена в выбранный день.',
                    ]);
                }

                /*
                 * Редактирование существующей строки в том же дне.
                 */
                if (
                    $source
                    && (int) $source->day_number
                        === $dayNumber
                ) {
                    $source->enabled = true;
                    $source->start_time = $start;
                    $source->end_time = $end;
                    $source->save();

                    return $source;
                }

                /*
                 * Перенос в другой день, где уже есть выключенная строка.
                 */
                if ($source && $target) {
                    $target->enabled = true;
                    $target->start_time = $start;
                    $target->end_time = $end;
                    $target->save();

                    $source->enabled = false;
                    $source->start_time = null;
                    $source->end_time = null;
                    $source->save();

                    return $target;
                }

                /*
                 * Перенос существующей строки в свободный день.
                 */
                if ($source) {
                    $source->day_number = $dayNumber;
                    $source->enabled = true;
                    $source->start_time = $start;
                    $source->end_time = $end;
                    $source->save();

                    return $source;
                }

                /*
                 * Новая смена.
                 */
                if ($target) {
                    $target->enabled = true;
                    $target->start_time = $start;
                    $target->end_time = $end;
                    $target->save();

                    return $target;
                }

                return EmployeeWorkSchedule::create([
                    'clinic_id' => $clinicId,
                    'employee_type' => 'doctor',
                    'employee_id' => $doctorId,
                    'day_number' => $dayNumber,
                    'enabled' => true,
                    'start_time' => $start,
                    'end_time' => $end,
                ]);
            }
        );

        return response()->json([
            'ok' => true,
            'schedule_id' => (int) $saved->id,
            'day' =>
                (int) $saved->day_number - 1,
            'start' => substr(
                (string) $saved->start_time,
                0,
                5
            ),
            'end' => substr(
                (string) $saved->end_time,
                0,
                5
            ),
        ]);
    }

    public function clinicSchedule()
    {
        $clinicId = (int) auth()->user()->doctor->clinic_id;
        $definitions = $this->clinicScheduleDefinitions();

        $buildings = ClinicBuilding::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->orderBy('number')
            ->orderBy('name')
            ->get([
                'id',
                'number',
                'name',
            ]);

        $officeRows = ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->with([
                'building',
                'mainDoctor',
            ])
            ->orderByRaw(
                "NULLIF(substring(cabinet from '^[0-9]+'), '')::integer ASC NULLS LAST"
            )
            ->orderBy('cabinet')
            ->get([
                'id',
                'building_id',
                'doctor_id',
                'cabinet',
                'name',
                'cabinet_type',
            ]);

        $buildingTabs = $buildings
            ->map(
                function (ClinicBuilding $building) use ($officeRows): array {
                    $number = trim((string) ($building->number ?? ''));
                    $name = trim((string) ($building->name ?? ''));

                    $baseLabel = $number !== ''
                        ? 'Корпус №' . $number
                        : 'Корпус #' . $building->id;

                    $label = $name !== ''
                        && mb_strtolower($name) !== mb_strtolower($baseLabel)
                            ? $baseLabel . ' — ' . $name
                            : $baseLabel;

                    return [
                        'key' => (string) $building->id,
                        'id' => (int) $building->id,
                        'label' => $label,
                        'offices_count' => $officeRows
                            ->where('building_id', $building->id)
                            ->count(),
                    ];
                }
            )
            ->values();

        $selectedBuildingKey = (string) request()->query(
            'building_id',
            'all'
        );

        $allowedBuildingKeys = $buildingTabs
            ->pluck('key')
            ->push('all')
            ->values()
            ->all();

        if (!in_array($selectedBuildingKey, $allowedBuildingKeys, true)) {
            $selectedBuildingKey = 'all';
        }

        $selectedBuildingTab = $selectedBuildingKey === 'all'
            ? null
            : $buildingTabs->firstWhere('key', $selectedBuildingKey);

        $selectedBuildingId = $selectedBuildingTab
            ? (int) $selectedBuildingTab['id']
            : null;

        $selectedBuildingLabel = $selectedBuildingTab['label']
            ?? 'Все корпуса';

        $weekDays = [];
        $workingDaysCount = null;
        $weekSummary = '';

        if ($selectedBuildingId) {
            $buildingScheduleRows = Schema::hasTable(
                'clinic_building_work_schedules'
            )
                ? ClinicBuildingWorkSchedule::query()
                    ->where('clinic_id', $clinicId)
                    ->where('building_id', $selectedBuildingId)
                    ->get()
                    ->keyBy('day_number')
                : collect();

            $legacyRows = ClinicWorkShedule::query()
                ->where('clinic_id', $clinicId)
                ->get()
                ->keyBy('day_number');

            $weekDays = collect($definitions)
                ->map(
                    static function (
                        array $definition,
                        int $dayNumber
                    ) use ($buildingScheduleRows, $legacyRows): array {
                        $row = $buildingScheduleRows->get($dayNumber)
                            ?? $legacyRows->get($dayNumber);

                        return [
                            'key' => $definition['key'],
                            'short' => $definition['short'],
                            'name' => $definition['name'],
                            'enabled' => $row
                                ? (bool) $row->enabled
                                : (bool) $definition['enabled'],
                            'from' => $row && $row->start_time
                                ? substr((string) $row->start_time, 0, 5)
                                : $definition['from'],
                            'to' => $row && $row->end_time
                                ? substr((string) $row->end_time, 0, 5)
                                : $definition['to'],
                        ];
                    }
                )
                ->values()
                ->all();

            $workingDaysCount = collect($weekDays)
                ->where('enabled', true)
                ->count();

            $weekSummary = collect($weekDays)
                ->filter(
                    static fn (array $day): bool =>
                        (bool) $day['enabled']
                )
                ->map(
                    static fn (array $day): string =>
                        $day['short']
                        . ' '
                        . $day['from']
                        . '–'
                        . $day['to']
                )
                ->implode(' · ');
        }

        $clinicOffices = $officeRows
            ->map(
                static function (ClinicCabinet $cabinet): array {
                    $officeLabel = $cabinet->cabinet
                        ? 'Кабинет №' . $cabinet->cabinet
                        : 'Кабинет #' . $cabinet->id;

                    if ($cabinet->name) {
                        $officeLabel .= ' — ' . $cabinet->name;
                    }

                    $buildingKey = $cabinet->building_id
                        ? (string) $cabinet->building_id
                        : 'unassigned';

                    $buildingLabel = 'Без корпуса';

                    if ($cabinet->building) {
                        $number = trim(
                            (string) ($cabinet->building->number ?? '')
                        );
                        $name = trim(
                            (string) ($cabinet->building->name ?? '')
                        );

                        $buildingLabel = $number !== ''
                            ? 'Корпус №' . $number
                            : 'Корпус #' . $cabinet->building->id;

                        if (
                            $name !== ''
                            && mb_strtolower($name)
                                !== mb_strtolower($buildingLabel)
                        ) {
                            $buildingLabel .= ' — ' . $name;
                        }
                    }

                    return [
                        'id' => (int) $cabinet->id,
                        'label' => $officeLabel,
                        'building_key' => $buildingKey,
                        'building_label' => $buildingLabel,
                        'doctor_id' => $cabinet->doctor_id
                            ? (int) $cabinet->doctor_id
                            : null,
                    ];
                }
            )
            ->values()
            ->all();

        $clinicDoctors = $this->clinicDoctorsQuery($clinicId)
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
            ->get()
            ->map(
                function (Doctor $doctor) use ($officeRows): array {
                    $speciality = $this->cleanDoctorSpeciality(
                        (string) ($doctor->status ?? '')
                    );

                    $label = trim(
                        ($doctor->full_name ?: 'Врач #' . $doctor->id)
                        . ($speciality !== '' ? ' — ' . $speciality : '')
                    );

                    $buildingKeys = $officeRows
                        ->where('doctor_id', $doctor->id)
                        ->map(
                            static fn (ClinicCabinet $cabinet): string =>
                                $cabinet->building_id
                                    ? (string) $cabinet->building_id
                                    : 'unassigned'
                        )
                        ->unique()
                        ->values()
                        ->all();

                    return [
                        'id' => (int) $doctor->id,
                        'label' => $label,
                        'building_keys' => $buildingKeys,
                    ];
                }
            )
            ->values()
            ->all();

        $clinicExceptions = collect();

        if (Schema::hasTable('clinic_schedule_exceptions')) {
            $clinicExceptions = ClinicScheduleException::query()
                ->where('clinic_id', $clinicId)
                ->orderByDesc('date_from')
                ->orderByDesc('time_from')
                ->orderByDesc('id')
                ->get()
                ->map(
                    fn (ClinicScheduleException $exception): array =>
                        $this->presentClinicScheduleException(
                            $exception,
                            $clinicOffices,
                            $clinicDoctors,
                            $buildingTabs->all()
                        )
                )
                ->values();
        }

        $clinicExceptionsForJs = $clinicExceptions
            ->mapWithKeys(
                static fn (array $exception): array => [
                    (string) $exception['id'] => $exception['form'],
                ]
            )
            ->all();

        $visibleExceptions = $clinicExceptions
            ->filter(
                static function (array $exception) use (
                    $selectedBuildingKey
                ): bool {
                    if ($selectedBuildingKey === 'all') {
                        return true;
                    }

                    $keys = collect($exception['building_keys'] ?? [])
                        ->map(static fn ($key): string => (string) $key);

                    return $keys->contains('all')
                        || $keys->contains($selectedBuildingKey);
                }
            )
            ->values();

        $today = Carbon::today();

        $nearestException = $visibleExceptions
            ->filter(
                static fn (array $exception): bool =>
                    Carbon::parse($exception['date_to'])
                        ->endOfDay()
                        ->gte($today)
            )
            ->sortBy(
                static fn (array $exception): string =>
                    $exception['date_from']
                    . ' '
                    . ($exception['form']['time_from'] ?? '00:00')
            )
            ->first();

        return $this->settingsView(
            'doctors.settings.clinic_schedule',
            compact(
                'weekDays',
                'workingDaysCount',
                'weekSummary',
                'buildingTabs',
                'selectedBuildingKey',
                'selectedBuildingId',
                'selectedBuildingLabel',
                'clinicDoctors',
                'clinicOffices',
                'clinicExceptions',
                'clinicExceptionsForJs',
                'nearestException'
            )
        );
    }

    public function clinicScheduleUpdate(Request $request)
    {
        abort_unless(
            Schema::hasTable('clinic_building_work_schedules'),
            503,
            'Таблица графиков корпусов ещё не создана. Выполните миграции.'
        );

        $clinicId = (int) auth()->user()->doctor->clinic_id;
        $definitions = $this->clinicScheduleDefinitions();

        $input = Validator::make(
            $request->all(),
            [
                'building_id' => [
                    'required',
                    'integer',
                    Rule::exists('clinic_buildings', 'id')
                        ->where('clinic_id', $clinicId),
                ],
                'schedule' => ['required', 'array'],
                'schedule.*' => ['array'],
                'schedule.*.enabled' => ['required', 'boolean'],
                'schedule.*.from' => ['nullable', 'date_format:H:i'],
                'schedule.*.to' => ['nullable', 'date_format:H:i'],
            ],
            [
                'building_id.required' => 'Выберите корпус.',
                'building_id.exists' => 'Выбранный корпус не относится к текущей клинике.',
                'schedule.required' => 'Не удалось получить график корпуса.',
                'schedule.*.enabled.required' => 'Не удалось определить состояние рабочего дня.',
                'schedule.*.from.date_format' => 'Время начала должно быть указано в формате ЧЧ:ММ.',
                'schedule.*.to.date_format' => 'Время окончания должно быть указано в формате ЧЧ:ММ.',
            ]
        )->validate();

        $buildingId = (int) $input['building_id'];

        $currentSchedule = ClinicBuildingWorkSchedule::query()
            ->where('clinic_id', $clinicId)
            ->where('building_id', $buildingId)
            ->get()
            ->keyBy('day_number');

        $legacySchedule = ClinicWorkShedule::query()
            ->where('clinic_id', $clinicId)
            ->get()
            ->keyBy('day_number');

        $errors = [];
        $prepared = [];

        foreach ($definitions as $dayNumber => $definition) {
            $code = $definition['key'];
            $row = (array) ($input['schedule'][$code] ?? []);
            $existing = $currentSchedule->get($dayNumber)
                ?? $legacySchedule->get($dayNumber);
            $enabled = filter_var(
                $row['enabled'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
            $from = trim((string) ($row['from'] ?? ''));
            $to = trim((string) ($row['to'] ?? ''));

            if ($from === '') {
                $from = $existing && $existing->start_time
                    ? substr((string) $existing->start_time, 0, 5)
                    : $definition['from'];
            }

            if ($to === '') {
                $to = $existing && $existing->end_time
                    ? substr((string) $existing->end_time, 0, 5)
                    : $definition['to'];
            }

            if ($enabled && $from >= $to) {
                $errors['schedule.' . $code . '.to'][] = sprintf(
                    'В %s время окончания работы должно быть позже времени начала.',
                    $definition['name']
                );
            }

            $prepared[$dayNumber] = [
                'enabled' => $enabled,
                'from' => $from,
                'to' => $to,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(
            function () use (
                $clinicId,
                $buildingId,
                $prepared
            ): void {
                foreach ($prepared as $dayNumber => $hours) {
                    ClinicBuildingWorkSchedule::updateOrCreate(
                        [
                            'clinic_id' => $clinicId,
                            'building_id' => $buildingId,
                            'day_number' => $dayNumber,
                        ],
                        [
                            'enabled' => (bool) $hours['enabled'],
                            'start_time' => $hours['from'],
                            'end_time' => $hours['to'],
                        ]
                    );
                }
            }
        );

        $building = ClinicBuilding::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail($buildingId);

        $message = sprintf(
            'График корпуса «%s» сохранён.',
            $building->name ?: ('#' . $building->id)
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route(
                'doctors.settings.clinic-schedule',
                ['building_id' => $buildingId]
            )
            ->with('success', $message);
    }

    public function clinicScheduleExceptionStore(
        Request $request
    ) {
        return $this->saveClinicScheduleException(
            $request
        );
    }

    public function clinicScheduleExceptionUpdate(
        Request $request,
        int $exception
    ) {
        return $this->saveClinicScheduleException(
            $request,
            $exception
        );
    }

    public function clinicScheduleExceptionDelete(
        int $exception
    ) {
        if (!Schema::hasTable('clinic_schedule_exceptions')) {
            return response()->json([
                'message' => 'Таблица особых периодов ещё не создана. Выполните php artisan migrate.',
            ], 503);
        }

        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $rule = ClinicScheduleException::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail($exception);

        $rule->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Ограничение удалено.',
        ]);
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

    public function vacationSchedule(Request $request)
    {
        $clinicId = (int) auth()->user()->doctor->clinic_id;
        $year = (int) $request->integer('year', now()->year);

        if ($year < 2020 || $year > 2100) {
            $year = now()->year;
        }

        $employees = $this->vacationEmployeesForClinic($clinicId);
        $employeeMap = $employees->keyBy('key');

        $replacementDoctors = $employees
            ->where('type', 'doctor')
            ->where('category', 'doctor')
            ->values();

        $replacementNurses = $employees
            ->where('type', 'nurse')
            ->values();

        $replacementDoctorMap = $replacementDoctors->keyBy('id');
        $replacementNurseMap = $replacementNurses->keyBy('id');

        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $vacations = Schema::hasTable('employee_vacations')
            ? EmployeeVacation::query()
                ->where('clinic_id', $clinicId)
                ->whereDate('date_from', '<=', $yearEnd->toDateString())
                ->whereDate('date_to', '>=', $yearStart->toDateString())
                ->orderBy('date_from')
                ->orderBy('employee_type')
                ->orderBy('employee_id')
                ->get()
            : collect();

        $decorateVacation = static function (
            EmployeeVacation $vacation
        ) use (
            $employeeMap,
            $replacementDoctorMap,
            $replacementNurseMap
        ): void {
            $employee = $employeeMap->get(
                $vacation->employee_type . ':' . $vacation->employee_id
            );

            $vacation->setAttribute(
                'employee_label',
                $employee['name'] ?? 'Сотрудник #' . $vacation->employee_id
            );

            $vacation->setAttribute(
                'employee_role',
                $employee['role'] ?? ''
            );

            $replacement = null;

            if (
                $vacation->employee_type === 'doctor'
                && $vacation->replace_doctor_id
            ) {
                $replacement = $replacementDoctorMap->get(
                    (int) $vacation->replace_doctor_id
                );
            } elseif (
                $vacation->employee_type === 'nurse'
                && $vacation->replace_nurse_id
            ) {
                $replacement = $replacementNurseMap->get(
                    (int) $vacation->replace_nurse_id
                );
            }

            $vacation->setAttribute(
                'replacement_employee_label',
                $replacement['name'] ?? ''
            );

            $vacation->setAttribute(
                'replacement_employee_role',
                $replacement['role'] ?? ''
            );
        };

        $vacations->each($decorateVacation);

        $nearestVacationStart = Carbon::today();
        $nearestVacationEnd = $nearestVacationStart
            ->copy()
            ->addMonthsNoOverflow(6)
            ->endOfDay();

        $nearestVacations = Schema::hasTable('employee_vacations')
            ? EmployeeVacation::query()
                ->where('clinic_id', $clinicId)
                ->whereDate(
                    'date_from',
                    '<=',
                    $nearestVacationEnd->toDateString()
                )
                ->whereDate(
                    'date_to',
                    '>=',
                    $nearestVacationStart->toDateString()
                )
                ->orderBy('date_from')
                ->orderBy('employee_type')
                ->orderBy('employee_id')
                ->get()
            : collect();

        $nearestVacations->each($decorateVacation);

        $vacationsByEmployee = $vacations
            ->groupBy(
                static fn (EmployeeVacation $vacation): string =>
                    $vacation->employee_type . ':' . $vacation->employee_id
            );

        $stats = [
            'employees' => $employees->count(),
            'vacations' => $vacations->count(),
            'currently_away' => $vacations
                ->filter(
                    static fn (EmployeeVacation $vacation): bool =>
                        $vacation->status === 'approved'
                        && now()->betweenIncluded(
                            $vacation->date_from->startOfDay(),
                            $vacation->date_to->endOfDay()
                        )
                )
                ->count(),
        ];

        return $this->settingsView(
            'doctors.settings.vacation_schedule',
            compact(
                'year',
                'employees',
                'vacations',
                'nearestVacations',
                'vacationsByEmployee',
                'replacementDoctors',
                'replacementNurses',
                'stats'
            )
        );
    }

    public function vacationScheduleStore(
        VacationScheduleStoreRequest $request
    ) {
        abort_unless(
            Schema::hasTable('employee_vacations'),
            503,
            'Таблица графика отпусков ещё не создана. Выполните миграции.'
        );

        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $data = $request->validated();
        $employeeType = (string) $data['employee_type'];
        $employeeId = (int) $data['employee_key'];

        $replaceDoctorId = $employeeType === 'doctor'
            && !empty($data['replace_doctor_id'])
                ? (int) $data['replace_doctor_id']
                : null;

        $replaceNurseId = $employeeType === 'nurse'
            && !empty($data['replace_nurse_id'])
                ? (int) $data['replace_nurse_id']
                : null;

        $replacementEmployeeId = $employeeType === 'doctor'
            ? $replaceDoctorId
            : $replaceNurseId;

        $this->ensureVacationDoesNotOverlap(
            $clinicId,
            $employeeType,
            $employeeId,
            $data['date_from'],
            $data['date_to']
        );

        $this->ensureVacationReplacementAvailable(
            $clinicId,
            $employeeType,
            $replacementEmployeeId,
            $data['date_from'],
            $data['date_to']
        );

        EmployeeVacation::create([
            'clinic_id' => $clinicId,
            'employee_type' => $employeeType,
            'employee_id' => $employeeId,
            'replacement_doctor_id' => $replaceDoctorId,
            'replacement_nurse_id' => $replaceNurseId,
            'vacation_type' => $data['vacation_type'],
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'status' => $data['status'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with(
            'success',
            'Отпуск добавлен в график.'
        );
    }

    public function vacationScheduleUpdate(
        VacationScheduleUpdateRequest $request,
        EmployeeVacation $vacation
    ) {
        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $data = $request->validated();
        $employeeType = (string) $data['employee_type'];
        $employeeId = (int) $data['employee_key'];

        $replaceDoctorId = $employeeType === 'doctor'
            && !empty($data['replace_doctor_id'])
                ? (int) $data['replace_doctor_id']
                : null;

        $replaceNurseId = $employeeType === 'nurse'
            && !empty($data['replace_nurse_id'])
                ? (int) $data['replace_nurse_id']
                : null;

        $replacementEmployeeId = $employeeType === 'doctor'
            ? $replaceDoctorId
            : $replaceNurseId;

        $this->ensureVacationDoesNotOverlap(
            $clinicId,
            $employeeType,
            $employeeId,
            $data['date_from'],
            $data['date_to'],
            (int) $vacation->id
        );

        $this->ensureVacationReplacementAvailable(
            $clinicId,
            $employeeType,
            $replacementEmployeeId,
            $data['date_from'],
            $data['date_to'],
            (int) $vacation->id
        );

        $vacation->update([
            'employee_type' => $employeeType,
            'employee_id' => $employeeId,
            'replace_doctor_id' => $replaceDoctorId,
            'replace_nurse_id' => $replaceNurseId,
            'vacation_type' => $data['vacation_type'],
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'status' => $data['status'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with(
            'success',
            'Период отпуска обновлён.'
        );
    }

    public function vacationScheduleDelete(
        VacationScheduleDeleteRequest $request,
        EmployeeVacation $vacation
    ) {
        $vacation->delete();

        return back()->with(
            'success',
            'Период отпуска удалён.'
        );
    }

}
