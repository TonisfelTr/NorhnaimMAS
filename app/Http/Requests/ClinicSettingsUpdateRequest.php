<?php

namespace App\Http\Requests;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClinicSettingsUpdateRequest extends FormRequest
{
    private const WEEK_DAYS = [
        'mon' => 'ПН',
        'tue' => 'ВТ',
        'wed' => 'СР',
        'thu' => 'ЧТ',
        'fri' => 'ПТ',
        'sat' => 'СБ',
        'sun' => 'ВС',
    ];

    public function authorize(): bool
    {
        $doctor = auth()->user()->doctor;

        return is_authed()
            && $doctor->clinic->headmaster_id == $doctor->id;
    }

    public function rules(): array
    {
        return [
            'clinic_type' => [
                'required',
                'string',
                Rule::in([
                    Clinic::TYPE_OUTPATIENT,
                    Clinic::TYPE_INPATIENT,
                ]),
            ],

            'inpatient_mode' => [
                'nullable',
                'string',
                Rule::in([
                    Clinic::INPATIENT_MODE_DAY,
                    Clinic::INPATIENT_MODE_NIGHT,
                    Clinic::INPATIENT_MODE_ROUND_THE_CLOCK,
                ]),
            ],

            'name' => 'required|string|max:255',
            // clinic-telemedicine-timezones:timezone-rules:v1:begin
            'timezone' => [
                'sometimes', 'nullable', 'string', 'max:64',
                Rule::in(\DateTimeZone::listIdentifiers(\DateTimeZone::ALL)),
            ],
            // clinic-telemedicine-timezones:timezone-rules:v1:end
            // clinic-benefit-requisites:rules:v1:begin
            'benefit_clinic' => [
                'sometimes',
                'array:prescribing_enabled,kind,ogrn,code,series,numbering_source,license_number,license_date,license_authority',
                'required_array_keys:prescribing_enabled,kind',
            ],
            'benefit_clinic.prescribing_enabled' => [
                'bail',
                'required_with:benefit_clinic',
                'boolean',
            ],
            'benefit_clinic.kind' => [
                'bail',
                'required_with:benefit_clinic',
                'string',
                Rule::in(['organization', 'individual']),
            ],
            'benefit_clinic.ogrn' => [
                'bail',
                'nullable',
                'string',
                $this->input('benefit_clinic.kind') === 'individual'
                    ? 'regex:/\A[0-9]{15}\z/'
                    : 'regex:/\A[0-9]{13}\z/',
            ],
            'benefit_clinic.code' => ['bail', 'nullable', 'string', 'max:50'],
            'benefit_clinic.series' => [
                'bail',
                'nullable',
                'string',
                'max:20',
                'regex:/\A[0-9]{2}[0-9A-ZА-ЯЁ\/-]*\z/iu',
            ],
            'benefit_clinic.numbering_source' => ['bail', 'nullable', 'string', 'max:1000'],
            'benefit_clinic.license_number' => ['bail', 'nullable', 'string', 'max:100'],
            'benefit_clinic.license_date' => ['bail', 'nullable', 'string', 'date_format:Y-m-d'],
            'benefit_clinic.license_authority' => ['bail', 'nullable', 'string', 'max:500'],
            // clinic-benefit-requisites:rules:v1:end
            // clinic-benefit-okato:rules:v1:begin
            'benefit_okato_prefix' => [
                'bail',
                'sometimes',
                'nullable',
                'string',
                'regex:/\A[0-9]{2}\z/',
            ],
            // clinic-benefit-okato:rules:v1:end
            'description' => 'nullable|string|max:10000',

            'address' => 'required|string|max:500',
            'address_confirmed' => 'required|accepted',
            'city' => 'nullable|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            'phones' => 'required|array|min:1|max:20',
            'phones.*.phone' => 'required|string|max:32',
            'phones.*.label' => 'nullable|string|max:120',

            'services' => [
                'nullable',
                'array',
                'min:1',
            ],
            'services.*' => [
                'required',
                'string',
                'distinct',
                Rule::in([
                    'in_person',
                    'telemedicine',
                ]),
            ],

            'working_hours' => 'nullable|array',
            'working_hours.*.enabled' => 'required|boolean',
            'working_hours.*.from' => 'nullable|date_format:H:i',
            'working_hours.*.to' => 'nullable|date_format:H:i',

            'inpatient_working_hours' => 'nullable|array',
            'inpatient_working_hours.*.enabled' => 'required|boolean',
            'inpatient_working_hours.*.from' => 'nullable|date_format:H:i',
            'inpatient_working_hours.*.to' => 'nullable|date_format:H:i',

            'inpatient_visiting_hours' => 'nullable|array',
            'inpatient_visiting_hours.*.enabled' => 'required|boolean',
            'inpatient_visiting_hours.*.from' => 'nullable|date_format:H:i',
            'inpatient_visiting_hours.*.to' => 'nullable|date_format:H:i',

            'employees' => 'nullable|array|max:500',
            'employees.*.id' => 'nullable|integer',
            'employees.*.surname' => 'required|string|max:100',
            'employees.*.name' => 'required|string|max:100',
            'employees.*.patronym' => 'nullable|string|max:100',
            'employees.*.position' => 'required|string|max:150',

            'employees.*.specialties' => 'nullable|array|max:20',
            'employees.*.specialties.*' => 'required|string|max:150',

            'employees.*.employment_status' => [
                'required',
                Rule::in([
                    'active',
                    'temporarily_absent',
                    'inactive',
                ]),
            ],

            'employees.*.phone' => 'nullable|string|max:32',
            'employees.*.email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'clinic_type.required' => 'Выберите тип клиники.',
            'clinic_type.in' => 'Выбран недопустимый тип клиники.',

            'inpatient_mode.in' => 'Выбран недопустимый режим стационара.',

            'name.required' => 'Укажите название клиники.',
            // clinic-telemedicine-timezones:timezone-messages:v1:begin
            'timezone.string' => 'Выберите часовой пояс клиники из списка.',
            'timezone.max' => 'Выберите часовой пояс клиники из списка.',
            'timezone.in' => 'Выберите действующий часовой пояс клиники из списка.',
            // clinic-telemedicine-timezones:timezone-messages:v1:end
            // clinic-benefit-requisites:messages:v1:begin
            'benefit_clinic.array' => 'Проверьте поля реквизитов для льготных рецептов.',
            'benefit_clinic.required_array_keys' => 'Укажите юридический статус и настройку оформления льготных рецептов.',
            'benefit_clinic.prescribing_enabled.required_with' => 'Укажите, включено ли оформление льготных рецептов.',
            'benefit_clinic.prescribing_enabled.boolean' => 'Выберите, включено ли оформление льготных рецептов.',
            'benefit_clinic.kind.required_with' => 'Выберите юридический статус клиники.',
            'benefit_clinic.kind.string' => 'Выберите юридический статус клиники из списка.',
            'benefit_clinic.kind.in' => 'Выберите юридическое лицо или индивидуального предпринимателя.',
            'benefit_clinic.ogrn.string' => 'Введите ОГРН или ОГРНИП цифрами без пробелов.',
            'benefit_clinic.ogrn.regex' => 'ОГРН юридического лица должен содержать 13 цифр, ОГРНИП — 15 цифр.',
            'benefit_clinic.code.string' => 'Введите код организации текстом.',
            'benefit_clinic.code.max' => 'Код организации не должен превышать 50 символов.',
            'benefit_clinic.series.string' => 'Введите серию рецептов текстом.',
            'benefit_clinic.series.max' => 'Серия рецептов не должна превышать 20 символов.',
            'benefit_clinic.series.regex' => 'Серия начинается с двух цифр; далее допустимы цифры, русские и латинские буквы, дефис и косая черта.',
            'benefit_clinic.numbering_source.string' => 'Укажите источник нумерации текстом.',
            'benefit_clinic.numbering_source.max' => 'Источник нумерации не должен превышать 1000 символов.',
            'benefit_clinic.license_number.string' => 'Введите номер лицензии текстом.',
            'benefit_clinic.license_number.max' => 'Номер лицензии не должен превышать 100 символов.',
            'benefit_clinic.license_date.string' => 'Укажите дату выдачи лицензии в формате ГГГГ-ММ-ДД.',
            'benefit_clinic.license_date.date_format' => 'Укажите существующую дату выдачи лицензии в формате ГГГГ-ММ-ДД.',
            'benefit_clinic.license_authority.string' => 'Укажите название лицензирующего органа текстом.',
            'benefit_clinic.license_authority.max' => 'Название лицензирующего органа не должно превышать 500 символов.',
            // clinic-benefit-requisites:messages:v1:end
            // clinic-benefit-okato:messages:v1:begin
            'benefit_okato_prefix.string' => 'Укажите первые две цифры ОКАТО.',
            'benefit_okato_prefix.regex' => 'Укажите ровно две цифры ОКАТО от 0 до 9 или оставьте поле пустым.',
            // clinic-benefit-okato:messages:v1:end

            'address.required' => 'Укажите адрес клиники.',
            'address_confirmed.accepted' => 'Выберите адрес из предложенного списка.',

            'phones.required' => 'Укажите хотя бы один телефон регистратуры.',
            'phones.min' => 'Укажите хотя бы один телефон регистратуры.',
            'phones.*.phone.required' => 'Укажите номер телефона.',

            'services.min' => 'Выберите хотя бы один формат оказания медицинской помощи.',

            'employees.*.surname.required' => 'Укажите фамилию сотрудника.',
            'employees.*.name.required' => 'Укажите имя сотрудника.',
            'employees.*.position.required' => 'Укажите должность сотрудника.',
            'employees.*.email.email' => 'Укажите корректный e-mail сотрудника.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'address_confirmed' => $this->boolean('address_confirmed'),
            'working_hours' => $this->normalizeSchedule(
                $this->input('working_hours', [])
            ),
            'inpatient_working_hours' => $this->normalizeSchedule(
                $this->input('inpatient_working_hours', [])
            ),
            'inpatient_visiting_hours' => $this->normalizeSchedule(
                $this->input('inpatient_visiting_hours', [])
            ),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $clinicType = (string) $this->input('clinic_type');

            if ($clinicType === Clinic::TYPE_OUTPATIENT) {
                $services = $this->input('services', []);

                if (!is_array($services) || !$services) {
                    $validator->errors()->add(
                        'services',
                        'Выберите хотя бы один формат оказания медицинской помощи.'
                    );
                }

                $this->validateSchedule(
                    $validator,
                    'working_hours',
                    'режим работы клиники',
                    true,
                    false
                );

                return;
            }

            if ($clinicType !== Clinic::TYPE_INPATIENT) {
                return;
            }

            $inpatientMode = (string) $this->input('inpatient_mode');

            if ($inpatientMode === '') {
                $validator->errors()->add(
                    'inpatient_mode',
                    'Выберите режим стационара.'
                );
            }

            if (
                $inpatientMode !== Clinic::INPATIENT_MODE_ROUND_THE_CLOCK
            ) {
                $this->validateSchedule(
                    $validator,
                    'inpatient_working_hours',
                    'режим работы стационара',
                    true,
                    $inpatientMode === Clinic::INPATIENT_MODE_NIGHT
                );
            }

            /*
             * На уровне клиники хранится базовое время посещений.
             * В дальнейшем отделение сможет переопределить этот график
             * своим собственным расписанием.
             */
            $this->validateSchedule(
                $validator,
                'inpatient_visiting_hours',
                'время посещения родственниками',
                true,
                false
            );
        });
    }

    private function normalizeSchedule($schedule): array
    {
        if (!is_array($schedule)) {
            return [];
        }

        foreach ($schedule as $day => $hours) {
            if (!is_array($hours)) {
                $schedule[$day] = [
                    'enabled' => false,
                    'from' => null,
                    'to' => null,
                ];

                continue;
            }

            $schedule[$day]['enabled'] = filter_var(
                $hours['enabled'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return $schedule;
    }

    private function validateSchedule(
        $validator,
        string $field,
        string $label,
        bool $requireEnabledDay,
        bool $allowOvernight
    ): void {
        $schedule = $this->input($field, []);

        if (!is_array($schedule)) {
            if ($requireEnabledDay) {
                $validator->errors()->add(
                    $field,
                    'Укажите ' . $label . '.'
                );
            }

            return;
        }

        $hasEnabledDay = false;

        foreach (self::WEEK_DAYS as $day => $dayLabel) {
            $hours = $schedule[$day] ?? [];

            if (empty($hours['enabled'])) {
                continue;
            }

            $hasEnabledDay = true;

            $from = $hours['from'] ?? null;
            $to = $hours['to'] ?? null;

            if (!$from) {
                $validator->errors()->add(
                    "{$field}.{$day}.from",
                    "В {$dayLabel} укажите время начала."
                );
            }

            if (!$to) {
                $validator->errors()->add(
                    "{$field}.{$day}.to",
                    "В {$dayLabel} укажите время окончания."
                );
            }

            if (!$from || !$to) {
                continue;
            }

            if ($allowOvernight) {
                if ($from === $to) {
                    $validator->errors()->add(
                        "{$field}.{$day}.to",
                        "В {$dayLabel} начало и окончание периода не могут совпадать."
                    );
                }

                continue;
            }

            if ($from >= $to) {
                $validator->errors()->add(
                    "{$field}.{$day}.to",
                    "В {$dayLabel} время окончания должно быть позже времени начала."
                );
            }
        }

        if ($requireEnabledDay && !$hasEnabledDay) {
            $validator->errors()->add(
                $field,
                'Укажите хотя бы один день для поля «' . $label . '».'
            );
        }
    }
}
