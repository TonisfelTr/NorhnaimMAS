<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use App\Models\HospitalDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class HospitalDepartmentRequest extends FormRequest
{
    private const DAY_CODES = [
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'sat',
        'sun',
    ];

    private const NON_DOCTOR_POSITIONS = [
        'Старшая медицинская сестра',
        'Медицинская сестра',
        'Процедурная медицинская сестра',
        'Санитар',
        'Медицинский регистратор',
    ];

    public function authorize(): bool
    {
        $doctor = auth()->user()?->doctor;

        return (bool) (
            $doctor
            && $doctor->clinic_id
            && $doctor->clinic?->clinic_type === 'inpatient'
        );
    }

    protected function prepareForValidation(): void
    {
        $visitingHours = $this->input('visiting_hours', []);

        if (!is_array($visitingHours)) {
            $visitingHours = [];
        }

        foreach (self::DAY_CODES as $code) {
            $row = isset($visitingHours[$code])
                && is_array($visitingHours[$code])
                    ? $visitingHours[$code]
                    : [];

            $visitingHours[$code] = [
                'enabled' => filter_var(
                    $row['enabled'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                ),
                'from' => !empty($row['from'])
                    ? (string) $row['from']
                    : null,
                'to' => !empty($row['to'])
                    ? (string) $row['to']
                    : null,
            ];
        }

        $headDoctorId = (int) $this->input('head_doctor_id', 0);

        $doctorIds = collect($this->input('doctor_ids', []))
            ->filter(
                static fn ($id): bool =>
                    is_numeric($id) && (int) $id > 0
            )
            ->map(
                static fn ($id): int => (int) $id
            )
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'use_clinic_visiting_hours' => filter_var(
                $this->input('use_clinic_visiting_hours', true),
                FILTER_VALIDATE_BOOLEAN
            ),
            'enabled' => filter_var(
                $this->input('enabled', true),
                FILTER_VALIDATE_BOOLEAN
            ),
            'visiting_hours' => $visitingHours,
            'doctor_ids' => $doctorIds,
        ]);
    }

    public function rules(): array
    {
        $clinicId = (int) auth()->user()->doctor->clinic_id;
        $departmentId = $this->departmentId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('hospital_departments', 'number')
                    ->where('clinic_id', $clinicId)
                    ->ignore($departmentId ?: null),
            ],

            'profile' => [
                'nullable',
                'string',
                'max:255',
            ],

            'patient_age_group' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['general', 'children', 'adolescents', 'adults']),
            ],

            'sex_type' => [
                'required',
                'string',
                Rule::in([
                    HospitalDepartment::SEX_TYPE_MALE,
                    HospitalDepartment::SEX_TYPE_FEMALE,
                    HospitalDepartment::SEX_TYPE_MIXED,
                ]),
            ],

            'building_id' => [
                'nullable',
                'integer',
                Rule::exists('hospital_buildings', 'id')
                    ->where('clinic_id', $clinicId),
            ],

            'head_doctor_id' => [
                'nullable',
                'integer',
                Rule::exists('doctors', 'id')
                    ->where('clinic_id', $clinicId),
                Rule::unique(
                    'hospital_departments',
                    'head_doctor_id'
                )
                    ->where('clinic_id', $clinicId)
                    ->ignore($departmentId ?: null),
            ],

            'head_nurse_id' => [
                'nullable',
                'integer',
                Rule::exists('nurses', 'id')
                    ->where('clinic_id', $clinicId),
                Rule::unique(
                    'hospital_departments',
                    'head_nurse_id'
                )
                    ->where('clinic_id', $clinicId)
                    ->ignore($departmentId ?: null),
            ],

            'doctor_ids' => [
                'nullable',
                'array',
                'max:200',
            ],

            'doctor_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('doctors', 'id')
                    ->where('clinic_id', $clinicId),
            ],

            'inpatient_mode' => [
                'required',
                'string',
                Rule::in([
                    'day',
                    'night',
                    'round_the_clock',
                ]),
            ],

            'use_clinic_visiting_hours' => [
                'required',
                'boolean',
            ],

            'visiting_hours' => [
                'nullable',
                'array',
            ],

            'visiting_hours.*' => [
                'array',
            ],

            'visiting_hours.*.enabled' => [
                'required',
                'boolean',
            ],

            'visiting_hours.*.from' => [
                'nullable',
                'date_format:H:i',
            ],

            'visiting_hours.*.to' => [
                'nullable',
                'date_format:H:i',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'enabled' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:100000',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateDepartmentDoctors($validator);
            $this->validateHeadNurseAvailability($validator);
            $this->validateVisitingHours($validator);
        });
    }

    private function validateDepartmentDoctors($validator): void
    {
        $clinicId = (int) auth()->user()->doctor->clinic_id;
        $departmentId = $this->departmentId();
        $headDoctorId = $this->filled('head_doctor_id')
            ? $this->integer('head_doctor_id')
            : null;
        $doctorIds = collect($this->input('doctor_ids', []))
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $allDoctorIds = $doctorIds
            ->when(
                $headDoctorId,
                static fn ($ids) => $ids->push((int) $headDoctorId)
            )
            ->unique()
            ->values();

        if ($allDoctorIds->isEmpty()) {
            return;
        }

        /*
         * Серверно подтверждаем, что это именно врачи, а не записи
         * медсестёр/регистраторов, которые тоже могут находиться в doctors.
         */
        $nonDoctorIds = Doctor::query()
            ->where('clinic_id', $clinicId)
            ->whereIn('id', $allDoctorIds)
            ->whereIn('profession', self::NON_DOCTOR_POSITIONS)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if ($headDoctorId && in_array($headDoctorId, $nonDoctorIds, true)) {
            $validator->errors()->add(
                'head_doctor_id',
                'Заведующим отделением можно выбрать только врача.'
            );
        }

        if ($doctorIds->intersect($nonDoctorIds)->isNotEmpty()) {
            $validator->errors()->add(
                'doctor_ids',
                'В список врачей отделения можно добавлять только врачей.'
            );
        }

        /*
         * Врач не может постоянно работать сразу в двух отделениях.
         * Проверяем и заведующих, и обычных врачей отделения.
         * Текущее отделение при редактировании исключаем.
         */
        $otherHeadAssignments = HospitalDepartment::query()
            ->where('clinic_id', $clinicId)
            ->when(
                $departmentId > 0,
                static fn ($query) => $query->where('id', '!=', $departmentId)
            )
            ->whereIn('head_doctor_id', $allDoctorIds)
            ->get([
                'id',
                'name',
                'head_doctor_id',
            ]);

        $otherMemberAssignments = DB::table(
            'hospital_department_doctors as hdd'
        )
            ->join(
                'hospital_departments as hd',
                'hd.id',
                '=',
                'hdd.hospital_department_id'
            )
            ->where('hd.clinic_id', $clinicId)
            ->when(
                $departmentId > 0,
                static fn ($query) =>
                    $query->where('hd.id', '!=', $departmentId)
            )
            ->whereIn('hdd.doctor_id', $allDoctorIds)
            ->get([
                'hdd.doctor_id',
                'hd.id as department_id',
                'hd.name as department_name',
            ]);

        $assignedElsewhere = [];

        foreach ($otherHeadAssignments as $department) {
            $assignedElsewhere[(int) $department->head_doctor_id] =
                (string) $department->name;
        }

        foreach ($otherMemberAssignments as $assignment) {
            $assignedElsewhere[(int) $assignment->doctor_id] =
                (string) $assignment->department_name;
        }

        if ($headDoctorId && isset($assignedElsewhere[$headDoctorId])) {
            $validator->errors()->add(
                'head_doctor_id',
                'Этот врач уже работает в отделении «'
                . $assignedElsewhere[$headDoctorId]
                . '».'
            );
        }

        $busyMemberIds = $doctorIds
            ->filter(
                static fn (int $id): bool =>
                    isset($assignedElsewhere[$id])
            )
            ->values();

        if ($busyMemberIds->isNotEmpty()) {
            $busyDoctors = Doctor::query()
                ->whereIn('id', $busyMemberIds)
                ->get([
                    'id',
                    'surname',
                    'name',
                    'patronym',
                ])
                ->map(
                    function (Doctor $doctor) use ($assignedElsewhere): string {
                        $name = trim(
                            implode(
                                ' ',
                                array_filter([
                                    $doctor->surname,
                                    $doctor->name,
                                    $doctor->patronym,
                                ])
                            )
                        );

                        return $name
                            . ' — «'
                            . ($assignedElsewhere[(int) $doctor->id] ?? 'другое отделение')
                            . '»';
                    }
                )
                ->implode('; ');

            $validator->errors()->add(
                'doctor_ids',
                'Нельзя добавить врачей, уже работающих в других отделениях: '
                . $busyDoctors
                . '.'
            );
        }

        /*
         * В стационар нельзя постоянно назначать врача, который уже
         * закреплён в амбулаторном контуре клиники:
         * кабинет, участок или выезды на дом.
         */
        $outpatientAssignments =
            $this->outpatientDoctorAssignments(
                $clinicId,
                $allDoctorIds->all()
            );

        if (
            $headDoctorId
            && isset($outpatientAssignments[$headDoctorId])
        ) {
            $validator->errors()->add(
                'head_doctor_id',
                'Этот врач уже работает в амбулаторном контуре: '
                . $outpatientAssignments[$headDoctorId]
                . '.'
            );
        }

        $outpatientMemberIds = $doctorIds
            ->filter(
                static fn (int $id): bool =>
                    isset($outpatientAssignments[$id])
            )
            ->values();

        if ($outpatientMemberIds->isNotEmpty()) {
            $labels = $outpatientMemberIds
                ->map(
                    fn (int $id): string =>
                        $outpatientAssignments[$id]
                )
                ->unique()
                ->implode('; ');

            $validator->errors()->add(
                'doctor_ids',
                'Нельзя добавить в стационар врачей амбулаторного контура: '
                . $labels
                . '.'
            );
        }
    }

    private function validateHeadNurseAvailability(
        $validator
    ): void {
        if (!$this->filled('head_nurse_id')) {
            return;
        }

        $clinicId = (int) auth()->user()->doctor->clinic_id;
        $nurseId = $this->integer('head_nurse_id');

        $assignment = $this->outpatientNurseAssignment(
            $clinicId,
            $nurseId
        );

        if ($assignment === '') {
            return;
        }

        $validator->errors()->add(
            'head_nurse_id',
            'Эта медсестра уже работает в амбулаторном контуре: '
            . $assignment
            . '.'
        );
    }

    private function outpatientDoctorAssignments(
        int $clinicId,
        array $doctorIds
    ): array {
        $doctorIds = collect($doctorIds)
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($doctorIds === []) {
            return [];
        }

        $result = [];

        if (Schema::hasTable('clinic_cabinets')) {
            $rows = DB::table('clinic_cabinets')
                ->where('clinic_id', $clinicId)
                ->whereIn('doctor_id', $doctorIds)
                ->get([
                    'id',
                    'cabinet',
                    'name',
                    'doctor_id',
                ]);

            foreach ($rows as $row) {
                $result[(int) $row->doctor_id] =
                    !empty($row->cabinet)
                        ? 'кабинет №' . $row->cabinet
                        : (
                            !empty($row->name)
                                ? (string) $row->name
                                : 'кабинет #' . $row->id
                        );
            }
        }

        if (Schema::hasTable('clinic_districts')) {
            $districts = DB::table('clinic_districts')
                ->where('clinic_id', $clinicId)
                ->where(
                    static function ($query) use ($doctorIds): void {
                        $query
                            ->whereIn('doctor_id', $doctorIds)
                            ->orWhereIn(
                                'home_visit_doctor_id',
                                $doctorIds
                            );
                    }
                )
                ->get([
                    'id',
                    'name',
                    'doctor_id',
                    'home_visit_doctor_id',
                ]);

            foreach ($districts as $district) {
                $label =
                    'участок «'
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
                    if (
                        !empty($doctorId)
                        && in_array(
                            (int) $doctorId,
                            $doctorIds,
                            true
                        )
                    ) {
                        $result[(int) $doctorId] = $label;
                    }
                }
            }

            if (Schema::hasTable('clinic_district_doctors')) {
                $rows = DB::table(
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
                    ->whereIn('cdd.doctor_id', $doctorIds)
                    ->get([
                        'cdd.doctor_id',
                        'cd.id as district_id',
                        'cd.name as district_name',
                    ]);

                foreach ($rows as $row) {
                    $result[(int) $row->doctor_id] =
                        'участок «'
                        . (
                            !empty($row->district_name)
                                ? (string) $row->district_name
                                : '#' . $row->district_id
                        )
                        . '»';
                }
            }
        }

        return $result;
    }

    private function outpatientNurseAssignment(
        int $clinicId,
        int $nurseId
    ): string {
        if (
            $nurseId <= 0
            || !Schema::hasTable('clinic_cabinets')
        ) {
            return '';
        }

        $row = DB::table('clinic_cabinets')
            ->where('clinic_id', $clinicId)
            ->where('nurse_id', $nurseId)
            ->first([
                'id',
                'cabinet',
                'name',
            ]);

        if (!$row) {
            return '';
        }

        return !empty($row->cabinet)
            ? 'кабинет №' . $row->cabinet
            : (
                !empty($row->name)
                    ? (string) $row->name
                    : 'кабинет #' . $row->id
            );
    }

    private function validateVisitingHours($validator): void
    {
        if ($this->boolean('use_clinic_visiting_hours')) {
            return;
        }

        $labels = [
            'mon' => 'ПН',
            'tue' => 'ВТ',
            'wed' => 'СР',
            'thu' => 'ЧТ',
            'fri' => 'ПТ',
            'sat' => 'СБ',
            'sun' => 'ВС',
        ];

        $hours = $this->input('visiting_hours', []);

        foreach (self::DAY_CODES as $code) {
            $row = isset($hours[$code]) && is_array($hours[$code])
                ? $hours[$code]
                : [];

            if (empty($row['enabled'])) {
                continue;
            }

            $from = $row['from'] ?? null;
            $to = $row['to'] ?? null;
            $label = $labels[$code] ?? mb_strtoupper($code);

            if (!$from) {
                $validator->errors()->add(
                    "visiting_hours.{$code}.from",
                    "Укажите начало посещений в {$label}."
                );
            }

            if (!$to) {
                $validator->errors()->add(
                    "visiting_hours.{$code}.to",
                    "Укажите окончание посещений в {$label}."
                );
            }

            if ($from && $to && $from >= $to) {
                $validator->errors()->add(
                    "visiting_hours.{$code}.to",
                    "В {$label} время окончания посещений должно быть позже времени начала."
                );
            }
        }
    }

    private function departmentId(): int
    {
        $routeDepartment = $this->route('department');

        return $routeDepartment instanceof HospitalDepartment
            ? (int) $routeDepartment->id
            : (int) $routeDepartment;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название отделения.',
            'name.max' => 'Название отделения не должно превышать 255 символов.',
            'number.unique' => 'Отделение с таким номером уже существует в этой клинике.',
            'number.max' => 'Номер отделения не должен превышать 50 символов.',
            'sex_type.required' => 'Укажите половой тип отделения.',
            'sex_type.in' => 'Выбран недопустимый половой тип отделения.',
            'patient_age_group.required' => 'Выберите возрастную группу отделения.',
            'patient_age_group.string' => 'Выбрана недопустимая возрастная группа отделения.',
            'patient_age_group.in' => 'Выбрана недопустимая возрастная группа отделения.',

            'building_id.exists' => 'Выбранный корпус стационара не относится к текущей клинике.',
            'head_doctor_id.exists' => 'Выбранный заведующий не относится к текущей клинике.',
            'head_doctor_id.unique' => 'Этот врач уже назначен заведующим другого отделения.',
            'head_nurse_id.exists' => 'Выбранная старшая медсестра не относится к текущей клинике.',
            'head_nurse_id.unique' => 'Эта медсестра уже назначена старшей медсестрой другого отделения.',
            'doctor_ids.array' => 'Некорректный список врачей отделения.',
            'doctor_ids.*.exists' => 'Один из выбранных врачей не относится к текущей клинике.',
            'doctor_ids.*.distinct' => 'Один врач выбран в отделение несколько раз.',
            'inpatient_mode.in' => 'Выбран недопустимый режим стационара.',
            'description.max' => 'Описание не должно превышать 2000 символов.',
        ];
    }
}
