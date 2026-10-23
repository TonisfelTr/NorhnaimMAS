<?php

namespace App\Http\Requests;

use App\Models\ClinicBuildingWorkSchedule;
use App\Models\ClinicCabinet;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\Nurse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ClinicOfficeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $doctor = auth()->user()?->doctor;

        /*
         * Доступ к doctors/settings уже ограничен middleware группы.
         * Здесь проверяем только, что текущий пользователь действительно
         * является сотрудником клиники. Иначе FormRequest сам возвращает 403
         * ещё до входа в CabinetController.
         */
        return $doctor
            && (int) $doctor->clinic_id > 0;
    }

    public function rules(): array
    {
        $clinicId = auth()->user()->doctor->clinic_id;

        $numberRule = Rule::unique('clinic_cabinets', 'cabinet')
            ->where('clinic_id', $clinicId);

        if (
            $this->input('mode') === 'edit'
            && $this->filled('office_id')
        ) {
            $numberRule->ignore($this->integer('office_id'));
        }

        $doctorRule = Rule::unique(
            'clinic_cabinets',
            'doctor_id'
        )->where('clinic_id', $clinicId);

        $nurseRule = Rule::unique(
            'clinic_cabinets',
            'nurse_id'
        )->where('clinic_id', $clinicId);

        $replaceDoctorRule = Rule::unique(
            'clinic_cabinets',
            'replace_doctor_id'
        )->where('clinic_id', $clinicId);

        $replaceNurseRule = Rule::unique(
            'clinic_cabinets',
            'replace_nurse_id'
        )->where('clinic_id', $clinicId);

        $clinicNurseUserIds = Doctor::where(
            'clinic_id',
            $clinicId
        )
            ->whereIn(
                'profession',
                [
                    'Старшая медицинская сестра',
                    'Медицинская сестра',
                    'Процедурная медицинская сестра',
                ]
            )
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $employedNurseIds = Nurse::where(
            'clinic_id',
            $clinicId
        )
            ->whereIn('user_id', $clinicNurseUserIds)
            ->pluck('id')
            ->all();

        $replacementNurseIds = Doctor::where(
            'clinic_id',
            $clinicId
        )
            ->whereIn('user_id', $clinicNurseUserIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $replacementDoctorIds = Doctor::where(
            'clinic_id',
            $clinicId
        )
            ->whereNotIn('id', $replacementNurseIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if (
            $this->input('mode') === 'edit'
            && $this->filled('office_id')
        ) {
            $officeId = $this->integer('office_id');

            $doctorRule->ignore($officeId);
            $nurseRule->ignore($officeId);
            $replaceDoctorRule->ignore($officeId);
            $replaceNurseRule->ignore($officeId);
        }

        return [
            'mode' => [
                'required',
                Rule::in([
                    'create',
                    'edit',
                ]),
            ],

            'office_id' => [
                'required_if:mode,edit',
                'nullable',
                'integer',
                Rule::exists('clinic_cabinets', 'id')
                    ->where('clinic_id', $clinicId),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'number' => [
                'required',
                'string',
                'max:50',
                $numberRule,
            ],

            'stage' => [
                'nullable',
                'integer',
            ],

            'building_id' => [
                'required',
                'integer',
                Rule::exists('clinic_buildings', 'id')
                    ->where('clinic_id', $clinicId),
            ],

            'cabinet_type' => [
                'required',
                Rule::in([
                    ClinicCabinet::TYPE_DOCTOR,
                    ClinicCabinet::TYPE_PROCEDURE,
                    ClinicCabinet::TYPE_FUNCTIONAL,
                ]),
            ],

            'phone' => 'nullable|string|max:32',

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'doctor' => [
                Rule::requiredIf(
                    function (): bool {
                        return in_array(
                            $this->input('cabinet_type'),
                            [
                                ClinicCabinet::TYPE_DOCTOR,
                                ClinicCabinet::TYPE_FUNCTIONAL,
                            ],
                            true
                        );
                    }
                ),
                'nullable',
                'integer',
                Rule::exists('doctors', 'id')
                    ->where('clinic_id', $clinicId),
                $doctorRule,
            ],

            'nurse' => [
                Rule::requiredIf(fn (): bool => $this->input('cabinet_type') === ClinicCabinet::TYPE_PROCEDURE),
                'nullable',
                'integer',
                Rule::exists('nurses', 'id')
                    ->where('clinic_id', $clinicId),
                Rule::in($employedNurseIds),
                $nurseRule,
            ],

            'replace_doctor_id' => [
                'nullable',
                'integer',
                Rule::exists('doctors', 'id')
                    ->where('clinic_id', $clinicId),
                Rule::in($replacementDoctorIds),
                $replaceDoctorRule,
            ],

            'replace_nurse_id' => [
                'nullable',
                'integer',
                Rule::exists('doctors', 'id')
                    ->where('clinic_id', $clinicId),
                Rule::in($replacementNurseIds),
                $replaceNurseRule,
            ],

            'functional_method' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('cabinet_type')
                        === ClinicCabinet::TYPE_FUNCTIONAL
                ),
                'nullable',
                'string',
                Rule::in(
                    array_keys(
                        ClinicCabinet::FUNCTIONAL_METHODS
                    )
                ),
            ],

            'doctor_schedule' => [
                'nullable',
                'array',
            ],

            'doctor_schedule.mon' => 'nullable|array',
            'doctor_schedule.tue' => 'nullable|array',
            'doctor_schedule.wed' => 'nullable|array',
            'doctor_schedule.thu' => 'nullable|array',
            'doctor_schedule.fri' => 'nullable|array',
            'doctor_schedule.sat' => 'nullable|array',
            'doctor_schedule.sun' => 'nullable|array',

            'doctor_schedule.*.enabled' => 'required|boolean',
            'doctor_schedule.*.from' => 'nullable|date_format:H:i',
            'doctor_schedule.*.to' => 'nullable|date_format:H:i',

            'procedure_schedule' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('cabinet_type')
                        === ClinicCabinet::TYPE_PROCEDURE
                ),
                'nullable',
                'array',
            ],

            'procedure_schedule.mon' => 'nullable|array',
            'procedure_schedule.tue' => 'nullable|array',
            'procedure_schedule.wed' => 'nullable|array',
            'procedure_schedule.thu' => 'nullable|array',
            'procedure_schedule.fri' => 'nullable|array',
            'procedure_schedule.sat' => 'nullable|array',
            'procedure_schedule.sun' => 'nullable|array',

            'procedure_schedule.*.enabled' => 'required|boolean',
            'procedure_schedule.*.from' => 'nullable|date_format:H:i',
            'procedure_schedule.*.to' => 'nullable|date_format:H:i',

            'recipe_148' => 'required|integer|min:0',
            'recipe_107' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'mode.required' => 'Не удалось определить режим сохранения кабинета.',
            'mode.in' => 'Некорректный режим сохранения кабинета.',

            'office_id.required_if' => 'Не удалось определить редактируемый кабинет.',
            'office_id.exists' => 'Редактируемый кабинет не найден в текущей клинике.',

            'name.required' => 'Укажите название кабинета.',
            'name.string' => 'Название кабинета должно быть строкой.',
            'name.max' => 'Название кабинета не должно превышать 255 символов.',

            'number.required' => 'Укажите номер кабинета.',
            'number.string' => 'Номер кабинета должен быть строкой.',
            'number.max' => 'Номер кабинета не должен превышать 50 символов.',
            'number.unique' => 'Кабинет с таким номером уже существует в этой клинике.',

            'stage.integer' => 'Этаж должен быть указан целым числом.',

            'building_id.required' => 'Выберите корпус кабинета.',
            'building_id.integer' => 'Указан некорректный корпус.',
            'building_id.exists' => 'Выбранный корпус не относится к текущей клинике.',

            'cabinet_type.required' => 'Выберите тип кабинета.',
            'cabinet_type.in' => 'Указан некорректный тип кабинета.',

            'status.required' => 'Укажите статус кабинета.',
            'status.in' => 'Указан некорректный статус кабинета.',

            'doctor.required' => 'Для врачебного кабинета или кабинета функциональной диагностики выберите врача.',
            'doctor.integer' => 'Указан некорректный врач.',
            'doctor.exists' => 'Можно назначить только врача из списка нанятых сотрудников текущей клиники.',
            'doctor.unique' => 'Этот врач уже назначен в другой кабинет.',

            'nurse.required_if' => 'Для процедурного кабинета выберите медицинскую сестру.',
            'nurse.integer' => 'Указана некорректная медицинская сестра.',
            'nurse.exists' => 'Указанная медицинская сестра не найдена в текущей клинике.',
            'nurse.in' => 'Можно назначить только медицинскую сестру, добавленную в список сотрудников текущей клиники.',
            'nurse.unique' => 'Эта медицинская сестра уже назначена в другой кабинет.',

            'replace_doctor_id.integer' =>
                'Указан некорректный замещающий врач.',
            'replace_doctor_id.exists' =>
                'Замещающим можно назначить только врача текущей клиники.',
            'replace_doctor_id.in' =>
                'Выбранный сотрудник недоступен как замещающий врач.',
            'replace_doctor_id.unique' =>
                'Этот врач уже назначен замещающим в другом кабинете.',
            'replace_nurse_id.integer' =>
                'Указана некорректная замещающая медицинская сестра.',
            'replace_nurse_id.exists' =>
                'Замещающей можно назначить только сотрудника текущей клиники.',
            'replace_nurse_id.in' =>
                'Выбранная сотрудница недоступна как замещающая медсестра.',
            'replace_nurse_id.unique' =>
                'Эта медсестра уже назначена замещающей в другом кабинете.',

            'functional_method.required' =>
                'Укажите метод функциональной диагностики.',
            'functional_method.in' =>
                'Указан неизвестный метод функциональной диагностики.',

            'doctor_schedule.*.enabled.boolean' => 'Некорректное состояние рабочего дня врача.',
            'doctor_schedule.*.from.date_format' => 'Время начала работы врача должно быть в формате ЧЧ:ММ.',
            'doctor_schedule.*.to.date_format' => 'Время окончания работы врача должно быть в формате ЧЧ:ММ.',

            'procedure_schedule.required_if' => 'Укажите расписание процедурного кабинета.',
            'procedure_schedule.*.enabled.boolean' => 'Некорректное состояние рабочего дня.',
            'procedure_schedule.*.from.date_format' => 'Время начала работы должно быть в формате ЧЧ:ММ.',
            'procedure_schedule.*.to.date_format' => 'Время окончания работы должно быть в формате ЧЧ:ММ.',

            'recipe_148.required' => 'Укажите количество бланков формы 148.',
            'recipe_148.integer' => 'Количество бланков формы 148 должно быть числом.',
            'recipe_148.min' => 'Количество бланков формы 148 не может быть отрицательным.',

            'recipe_107.required' => 'Укажите количество бланков формы 107.',
            'recipe_107.integer' => 'Количество бланков формы 107 должно быть числом.',
            'recipe_107.min' => 'Количество бланков формы 107 не может быть отрицательным.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $procedureSchedule = $this->input('procedure_schedule', []);
        $doctorSchedule = $this->input('doctor_schedule', []);

        foreach ([&$procedureSchedule, &$doctorSchedule] as &$schedule) {
            if (!is_array($schedule)) {
                continue;
            }

            foreach ($schedule as $day => $hours) {
                if (!is_array($hours)) {
                    continue;
                }

                $schedule[$day]['enabled'] = filter_var(
                    $hours['enabled'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );
            }
        }

        $this->merge([
            'procedure_schedule' => $procedureSchedule,
            'doctor_schedule' => $doctorSchedule,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $clinicId = auth()->user()->doctor->clinic_id;

            if ($this->input('mode') === 'edit') {
                if (
                    $this->filled('replace_doctor_id')
                    && $this->filled('doctor')
                    && $this->integer('replace_doctor_id')
                        === $this->integer('doctor')
                ) {
                    $validator->errors()->add(
                        'replace_doctor_id',
                        'Основной врач не может быть назначен своим же замещающим.'
                    );
                }

                if (
                    $this->input('cabinet_type')
                    === ClinicCabinet::TYPE_PROCEDURE
                    && $this->filled('replace_doctor_id')
                ) {
                    $validator->errors()->add(
                        'replace_doctor_id',
                        'Для процедурного кабинета замещающий врач не используется.'
                    );
                }

                if (
                    $this->filled('replace_nurse_id')
                    && !$this->filled('nurse')
                ) {
                    $validator->errors()->add(
                        'replace_nurse_id',
                        'Сначала назначьте основную медсестру кабинета.'
                    );
                }

                if (
                    $this->filled('replace_nurse_id')
                    && $this->filled('nurse')
                ) {
                    $mainNurse = Nurse::where(
                        'clinic_id',
                        $clinicId
                    )
                        ->find(
                            $this->integer('nurse')
                        );

                    $mainNurseDoctorId = $mainNurse
                        ? Doctor::where(
                            'clinic_id',
                            $clinicId
                        )
                            ->where(
                                'user_id',
                                $mainNurse->user_id
                            )
                            ->value('id')
                        : null;

                    if (
                        $mainNurseDoctorId
                        && (int) $mainNurseDoctorId
                            === $this->integer(
                                'replace_nurse_id'
                            )
                    ) {
                        $validator->errors()->add(
                            'replace_nurse_id',
                            'Основная медсестра не может быть назначена своей же замещающей.'
                        );
                    }
                }
            }

            $clinicSchedule = ClinicWorkShedule::where(
                'clinic_id',
                $clinicId
            )
                ->get()
                ->keyBy('day_number');

            $buildingId = $this->integer('building_id');

            if (
                $buildingId > 0
                && Schema::hasTable('clinic_building_work_schedules')
            ) {
                $buildingSchedule = ClinicBuildingWorkSchedule::query()
                    ->where('clinic_id', $clinicId)
                    ->where('building_id', $buildingId)
                    ->get()
                    ->keyBy('day_number');

                foreach ($buildingSchedule as $dayNumber => $row) {
                    $clinicSchedule->put($dayNumber, $row);
                }
            }

            if (
                in_array(
                    $this->input('cabinet_type'),
                    [
                        ClinicCabinet::TYPE_DOCTOR,
                        ClinicCabinet::TYPE_FUNCTIONAL,
                    ],
                    true
                )
            ) {
                $schedule = $this->input('doctor_schedule', []);
                $days = [
                    'mon' => 'ПН', 'tue' => 'ВТ', 'wed' => 'СР',
                    'thu' => 'ЧТ', 'fri' => 'ПТ', 'sat' => 'СБ', 'sun' => 'ВС',
                ];

                if (is_array($schedule)) {
                    foreach ($schedule as $day => $hours) {
                        if (empty($hours['enabled'])) {
                            continue;
                        }

                        $from = $hours['from'] ?? null;
                        $to = $hours['to'] ?? null;
                        $label = $days[$day] ?? mb_strtoupper($day);

                        if (!$from) {
                            $validator->errors()->add(
                                "doctor_schedule.{$day}.from",
                                "Укажите время начала работы врача в {$label}."
                            );
                        }

                        if (!$to) {
                            $validator->errors()->add(
                                "doctor_schedule.{$day}.to",
                                "Укажите время окончания работы врача в {$label}."
                            );
                        }

                        if (!$from || !$to) {
                            continue;
                        }

                        if ($from >= $to) {
                            $validator->errors()->add(
                                "doctor_schedule.{$day}.to",
                                "В {$label} время окончания работы врача должно быть позже времени начала."
                            );
                            continue;
                        }

                        $dayNumber = ClinicWorkShedule::dayToNumber($day);
                        $clinicDay = $clinicSchedule->get($dayNumber);

                        if (!$clinicDay || !$clinicDay->enabled) {
                            $validator->errors()->add(
                                "doctor_schedule.{$day}.enabled",
                                "Нельзя назначить работу врача в {$label}: корпус в этот день не работает."
                            );
                            continue;
                        }

                        $clinicFrom = substr((string) $clinicDay->start_time, 0, 5);
                        $clinicTo = substr((string) $clinicDay->end_time, 0, 5);

                        if ($from < $clinicFrom) {
                            $validator->errors()->add(
                                "doctor_schedule.{$day}.from",
                                "В {$label} врач не может начинать работу раньше корпуса. Корпус работает с {$clinicFrom}."
                            );
                        }

                        if ($to > $clinicTo) {
                            $validator->errors()->add(
                                "doctor_schedule.{$day}.to",
                                "В {$label} врач не может работать после закрытия корпуса. Корпус работает до {$clinicTo}."
                            );
                        }
                    }
                }

                return;
            }

            if (
                $this->input('cabinet_type')
                !== ClinicCabinet::TYPE_PROCEDURE
            ) {
                return;
            }

            $schedule = $this->input('procedure_schedule', []);

            if (!is_array($schedule)) {
                return;
            }

            $days = [
                'mon' => 'ПН',
                'tue' => 'ВТ',
                'wed' => 'СР',
                'thu' => 'ЧТ',
                'fri' => 'ПТ',
                'sat' => 'СБ',
                'sun' => 'ВС',
            ];

            $hasWorkingDay = false;

            foreach ($schedule as $day => $hours) {
                if (empty($hours['enabled'])) {
                    continue;
                }

                $hasWorkingDay = true;

                $from = $hours['from'] ?? null;
                $to = $hours['to'] ?? null;
                $dayLabel = $days[$day] ?? mb_strtoupper($day);

                if (!$from) {
                    $validator->errors()->add(
                        "procedure_schedule.{$day}.from",
                        "Укажите время начала работы процедурного кабинета в {$dayLabel}."
                    );
                }

                if (!$to) {
                    $validator->errors()->add(
                        "procedure_schedule.{$day}.to",
                        "Укажите время окончания работы процедурного кабинета в {$dayLabel}."
                    );
                }

                if (!$from || !$to) {
                    continue;
                }

                if ($from >= $to) {
                    $validator->errors()->add(
                        "procedure_schedule.{$day}.to",
                        "В {$dayLabel} время окончания работы кабинета должно быть позже времени начала."
                    );

                    continue;
                }

                $dayNumber = ClinicWorkShedule::dayToNumber($day);
                $clinicDay = $clinicSchedule->get($dayNumber);

                if (!$clinicDay || !$clinicDay->enabled) {
                    $validator->errors()->add(
                        "procedure_schedule.{$day}.enabled",
                        "Нельзя включить работу процедурного кабинета в {$dayLabel}: корпус в этот день не работает."
                    );

                    continue;
                }

                $clinicFrom = substr((string) $clinicDay->start_time, 0, 5);
                $clinicTo = substr((string) $clinicDay->end_time, 0, 5);

                if ($from < $clinicFrom) {
                    $validator->errors()->add(
                        "procedure_schedule.{$day}.from",
                        "В {$dayLabel} процедурный кабинет не может начинать работу раньше корпуса. Корпус работает с {$clinicFrom}."
                    );
                }

                if ($to > $clinicTo) {
                    $validator->errors()->add(
                        "procedure_schedule.{$day}.to",
                        "В {$dayLabel} процедурный кабинет не может работать после закрытия корпуса. Корпус работает до {$clinicTo}."
                    );
                }
            }

            if (!$hasWorkingDay) {
                $validator->errors()->add(
                    'procedure_schedule',
                    'Укажите хотя бы один рабочий день процедурного кабинета.'
                );
            }
        });
    }
}
