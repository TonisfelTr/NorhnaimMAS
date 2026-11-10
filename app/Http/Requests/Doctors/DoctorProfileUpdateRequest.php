<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctors;

use Illuminate\Foundation\Http\FormRequest;

final class DoctorProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $digits = preg_replace(
            '/\D+/',
            '',
            (string) $this->input('snils', '')
        );

        $snils = null;

        if ($digits !== '') {
            $snils = strlen($digits) === 11
                ? substr($digits, 0, 3)
                    . '-'
                    . substr($digits, 3, 3)
                    . '-'
                    . substr($digits, 6, 3)
                    . ' '
                    . substr($digits, 9, 2)
                : (string) $this->input('snils');
        }

        $this->merge([
            'snils' => $snils,
            'ready_for_telemedicine' =>
                $this->boolean('ready_for_telemedicine'),
        ]);
    }

    public function rules(): array
    {
        $doctor = $this->user()?->doctor;
        $hasClinic = (bool) $doctor?->clinic_id;

        return [
            'surname' => [
                'required',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'patronym' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_at' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address_job' => [
                $hasClinic ? 'nullable' : 'required',
                'string',
                'max:2000',
            ],

            'experience_years' => [
                'nullable',
                'integer',
                'min:0',
                'max:80',
            ],

            'experience_months' => [
                'nullable',
                'integer',
                'min:0',
                'max:11',
            ],

            'about' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'snils' => [
                'nullable',
                'regex:/^\d{3}-\d{3}-\d{3} \d{2}$/',
            ],

            'ready_for_telemedicine' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'surname.required' =>
                'Укажите фамилию.',

            'name.required' =>
                'Укажите имя.',

            'birth_at.required' =>
                'Укажите дату рождения.',

            'birth_at.before_or_equal' =>
                'Дата рождения не может быть в будущем.',

            'address_job.required' =>
                'Для врача без клиники укажите место работы.',

            'email.email' =>
                'Укажите корректный адрес электронной почты.',

            'experience_years.max' =>
                'Проверьте количество полных лет стажа.',

            'experience_months.max' =>
                'Количество дополнительных месяцев должно быть от 0 до 11.',

            'snils.regex' =>
                'СНИЛС должен содержать 11 цифр.',
        ];
    }
}
