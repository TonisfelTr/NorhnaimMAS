<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClinicSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $doctor = auth()->user()->doctor;
        return is_authed() && $doctor->clinic->headmaster_id == $doctor->id ;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',

            'address' => 'required|string|max:500',
            'address_confirmed' => 'required|accepted',
            'city' => 'nullable|string|max:150',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            'phones' => 'required|array|min:1|max:20',
            'phones.*.phone' => 'required|string|max:32',
            'phones.*.label' => 'nullable|string|max:120',

            'services' => 'required|array|min:1',
            'services.*' => [
                'required',
                'string',
                'distinct',
                Rule::in([
                    'in_person',
                    'telemedicine',
                ]),
            ],

            'working_hours' => 'required|array',
            'working_hours.mon' => 'required|array',
            'working_hours.tue' => 'required|array',
            'working_hours.wed' => 'required|array',
            'working_hours.thu' => 'required|array',
            'working_hours.fri' => 'required|array',
            'working_hours.sat' => 'required|array',
            'working_hours.sun' => 'required|array',

            'working_hours.*.enabled' => 'required|boolean',
            'working_hours.*.from' => 'nullable|date_format:H:i',
            'working_hours.*.to' => 'nullable|date_format:H:i',

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
                    'dismissed',
                ]),
            ],

            'employees.*.dismissed_at' => 'nullable|date',
            'employees.*.phone' => 'nullable|string|max:32',
            'employees.*.email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название клиники.',

            'address.required' => 'Укажите адрес клиники.',
            'address_confirmed.accepted' => 'Выберите адрес из предложенного списка.',
            'latitude.required' => 'Не удалось определить координаты клиники.',
            'longitude.required' => 'Не удалось определить координаты клиники.',

            'phones.required' => 'Укажите хотя бы один телефон регистратуры.',
            'phones.min' => 'Укажите хотя бы один телефон регистратуры.',
            'phones.*.phone.required' => 'Укажите номер телефона.',

            'services.required' => 'Выберите хотя бы один формат оказания медицинской помощи.',
            'services.min' => 'Выберите хотя бы один формат оказания медицинской помощи.',

            'employees.*.surname.required' => 'Укажите фамилию сотрудника.',
            'employees.*.name.required' => 'Укажите имя сотрудника.',
            'employees.*.position.required' => 'Укажите должность сотрудника.',
            'employees.*.email.email' => 'Укажите корректный e-mail сотрудника.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $workingHours = $this->input('working_hours', []);

        if (is_array($workingHours)) {
            foreach ($workingHours as $day => $hours) {
                if (!is_array($hours)) {
                    continue;
                }

                $workingHours[$day]['enabled'] = filter_var(
                    $hours['enabled'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );
            }
        }

        $this->merge([
            'address_confirmed' => $this->boolean('address_confirmed'),
            'working_hours' => $workingHours,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $workingHours = $this->input('working_hours', []);

            if (!is_array($workingHours)) {
                return;
            }

            $hasWorkingDay = false;

            foreach ([
                         'mon',
                         'tue',
                         'wed',
                         'thu',
                         'fri',
                         'sat',
                         'sun',
                     ] as $day) {
                $hours = $workingHours[$day] ?? [];

                if (empty($hours['enabled'])) {
                    continue;
                }

                $hasWorkingDay = true;

                $from = $hours['from'] ?? null;
                $to = $hours['to'] ?? null;

                if (!$from) {
                    $validator->errors()->add(
                        "working_hours.{$day}.from",
                        'Укажите время начала работы.'
                    );
                }

                if (!$to) {
                    $validator->errors()->add(
                        "working_hours.{$day}.to",
                        'Укажите время окончания работы.'
                    );
                }

                if ($from && $to && $from >= $to) {
                    $validator->errors()->add(
                        "working_hours.{$day}.to",
                        'Время окончания работы должно быть позже времени начала.'
                    );
                }
            }

            if (!$hasWorkingDay) {
                $validator->errors()->add(
                    'working_hours',
                    'Укажите хотя бы один рабочий день клиники.'
                );
            }
        });
    }
}
