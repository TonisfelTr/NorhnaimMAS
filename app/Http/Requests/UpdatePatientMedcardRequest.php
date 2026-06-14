<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientMedcardRequest extends FormRequest
{
    private Patient $patient;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->patient = $this->route('patient');

        /** @todo необходимо будет продумать систему доступов к медкартам и,
         *        лучше их делать в мидлваре, а не тут.
         */

        return !app()->hasDebugModeEnabled() ? $this->patient->doctor_id == auth()->user()->doctor->id : true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Основные данные
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'patronym' => ['nullable', 'string', 'max:255'],

            'birth_at' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:255'],

            'gender' => ['nullable', Rule::in(['M', 'F'])],

            'medcard_number' => ['nullable', 'string', 'max:100'],
            'snils' => ['nullable', 'string', 'max:14'],

            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->ignore($this->patient->id),
            ],

            'insurance_company' => ['nullable', 'string', 'max:255'],
            'oms' => ['nullable', 'string', 'max:32'],

            // Социальные и трудовые сведения
            'profession' => ['nullable', 'string', 'max:255'],
            'job_organization' => ['nullable', 'string', 'max:500'],

            'socially_dangerous' => ['boolean'],
            'disability' => ['boolean'],
            'married' => ['boolean'],

            // Паспорт
            'series' => ['nullable', 'string', 'max:4'],
            'number' => ['nullable', 'string', 'max:6'],
            'issued_at' => ['nullable', 'date', 'before_or_equal:today'],
            'department_code' => ['nullable', 'string', 'max:7'],
            'issued_by' => ['nullable', 'string', 'max:500'],

            // Адреса
            'address_registration' => ['nullable', 'string', 'max:255'],
            'address_residence' => ['nullable', 'string', 'max:255'],

            // Комментарий
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'surname' => 'фамилия',
            'name' => 'имя',
            'patronym' => 'отчество',
            'birth_at' => 'дата рождения',
            'birth_place' => 'место рождения',
            'gender' => 'пол',
            'medical_card_number' => 'номер медкарты',
            'snils' => 'СНИЛС',
            'email' => 'email',
            'insurance_company' => 'страховая организация',
            'oms' => 'номер полиса',
            'profession' => 'профессия',
            'job_organization' => 'место работы / организация',
            'socially_dangerous' => 'социально опасный',
            'disability' => 'инвалидность',
            'married' => 'состоит в браке',
            'series' => 'серия паспорта',
            'number' => 'номер паспорта',
            'issued_at' => 'дата выдачи паспорта',
            'department_code' => 'код подразделения',
            'issued_by' => 'кем выдан паспорт',
            'address_registration' => 'адрес прописки',
            'address_residence' => 'адрес фактического проживания',
            'comment' => 'комментарий',
        ];
    }
}
