<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class UpdateRegistryRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'patronym' => 'required|string|max:255',
            'birth_at' => 'required|date|before_or_equal:today',
            'appointment_datetime' => 'required|date|after_or_equal:now',
            'address_registration' => 'required|string',
            'address_residence' => 'required|string',
            'birth_place' => 'required|string',
            'snils' => 'required|string|max:14',
            'oms' => 'required|string|max:16',
            'doctor_id' => 'required|exists:doctors,id',

            'patient_document_type' => 'required|in:passport_rf,birth_certificate,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
            'patient_document_name' => 'nullable|string|max:255',
            'patient_document_series' => 'nullable|string|max:50',
            'patient_document_number' => 'required|string|max:100',
            'patient_document_department_code' => 'nullable|string|max:20',
            'patient_document_issued_by' => 'required|string',
            'patient_document_issued_at' => 'required|date|before_or_equal:today',

            'representative_type' => 'nullable|in:mother,father,adoptive_parent,guardian,trustee,authorized,other',
            'representative_surname' => 'nullable|string|max:255',
            'representative_name' => 'nullable|string|max:255',
            'representative_patronym' => 'nullable|string|max:255',
            'representative_birth_at' => 'nullable|date|before_or_equal:today',
            'representative_snils' => 'nullable|string|max:14',
            'representative_phone' => 'nullable|string|max:50',
            'representative_email' => 'nullable|email|max:255',

            'representative_identity_document_type' => 'nullable|in:passport_rf,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
            'representative_identity_document_name' => 'nullable|string|max:255',
            'representative_identity_series' => 'nullable|string|max:50',
            'representative_identity_number' => 'nullable|string|max:100',
            'representative_identity_department_code' => 'nullable|string|max:20',
            'representative_identity_issued_by' => 'nullable|string',
            'representative_identity_issued_at' => 'nullable|date|before_or_equal:today',

            'representative_authority_document_type' => 'nullable|in:guardianship_order,power_of_attorney,other',
            'representative_authority_document_name' => 'nullable|string|max:255',
            'representative_authority_series' => 'nullable|string|max:50',
            'representative_authority_number' => 'nullable|string|max:100',
            'representative_authority_issued_by' => 'nullable|string',
            'representative_authority_issued_at' => 'nullable|date|before_or_equal:today',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                $this->input('patient_document_type') === 'passport_rf'
                && !$this->filled('patient_document_department_code')
            ) {
                $validator->errors()->add(
                    'patient_document_department_code',
                    'Для паспорта гражданина РФ необходимо указать код подразделения.'
                );
            }

            if (
                $this->input('patient_document_type') === 'other'
                && !$this->filled('patient_document_name')
            ) {
                $validator->errors()->add(
                    'patient_document_name',
                    'Для другого типа документа укажите его наименование.'
                );
            }

            if (!$this->filled('birth_at')) {
                return;
            }

            try {
                $age = Carbon::parse($this->input('birth_at'))->age;
            } catch (\Throwable) {
                return;
            }

            if ($age >= 18) {
                return;
            }

            foreach ([
                'representative_type' => 'Тип представителя',
                'representative_surname' => 'Фамилия представителя',
                'representative_name' => 'Имя представителя',
                'representative_birth_at' => 'Дата рождения представителя',
                'representative_identity_document_type' => 'Тип документа представителя',
                'representative_identity_number' => 'Номер документа представителя',
                'representative_identity_issued_by' => 'Кем выдан документ представителя',
                'representative_identity_issued_at' => 'Дата выдачи документа представителя',
            ] as $field => $label) {
                if (!$this->filled($field)) {
                    $validator->errors()->add(
                        $field,
                        "Поле \"{$label}\" обязательно для несовершеннолетнего пациента."
                    );
                }
            }

            if (
                $this->input('representative_identity_document_type') === 'passport_rf'
                && !$this->filled('representative_identity_department_code')
            ) {
                $validator->errors()->add(
                    'representative_identity_department_code',
                    'Для паспорта представителя необходимо указать код подразделения.'
                );
            }

            if (
                $this->input('representative_identity_document_type') === 'other'
                && !$this->filled('representative_identity_document_name')
            ) {
                $validator->errors()->add(
                    'representative_identity_document_name',
                    'Для другого типа документа представителя укажите его наименование.'
                );
            }

            $needsAuthorityDocument = in_array(
                $this->input('representative_type'),
                ['guardian', 'trustee', 'authorized', 'other'],
                true
            );

            if (!$needsAuthorityDocument) {
                return;
            }

            foreach ([
                'representative_authority_document_type' => 'Тип документа о полномочиях',
                'representative_authority_number' => 'Номер документа о полномочиях',
                'representative_authority_issued_by' => 'Кем выдан документ о полномочиях',
                'representative_authority_issued_at' => 'Дата выдачи документа о полномочиях',
            ] as $field => $label) {
                if (!$this->filled($field)) {
                    $validator->errors()->add(
                        $field,
                        "Поле \"{$label}\" обязательно для выбранного типа представителя."
                    );
                }
            }

            if (
                $this->input('representative_authority_document_type') === 'other'
                && !$this->filled('representative_authority_document_name')
            ) {
                $validator->errors()->add(
                    'representative_authority_document_name',
                    'Укажите наименование документа, подтверждающего полномочия.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле ":attribute" обязательно для заполнения.',
            'string' => 'Поле ":attribute" должно быть строкой.',
            'max' => 'Поле ":attribute" не должно превышать :max символов.',
            'date' => 'Поле ":attribute" должно быть датой.',
            'email' => 'Поле ":attribute" должно содержать корректный e-mail.',
            'in' => 'Для поля ":attribute" выбрано недопустимое значение.',
            'before_or_equal' => 'Поле ":attribute" не может содержать будущую дату.',
            'after_or_equal' => 'Поле ":attribute" должно быть датой не раньше текущего момента.',
            'exists' => 'Значение поля ":attribute" не зарегистрировано в базе данных.',
        ];
    }

    public function attributes(): array
    {
        return [
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronym' => 'Отчество',
            'birth_at' => 'Дата рождения',
            'appointment_datetime' => 'Дата приёма',
            'address_registration' => 'Адрес регистрации',
            'address_residence' => 'Адрес проживания',
            'birth_place' => 'Место рождения',
            'snils' => 'СНИЛС',
            'oms' => 'ОМС',
            'doctor_id' => 'Лечащий врач',
            'patient_document_type' => 'Тип документа пациента',
            'patient_document_name' => 'Наименование документа пациента',
            'patient_document_number' => 'Номер документа пациента',
            'patient_document_department_code' => 'Код подразделения документа пациента',
            'patient_document_issued_by' => 'Кем выдан документ пациента',
            'patient_document_issued_at' => 'Дата выдачи документа пациента',
            'representative_type' => 'Тип представителя',
            'representative_surname' => 'Фамилия представителя',
            'representative_name' => 'Имя представителя',
            'representative_birth_at' => 'Дата рождения представителя',
            'representative_identity_document_type' => 'Тип документа представителя',
            'representative_identity_document_name' => 'Наименование документа представителя',
            'representative_identity_number' => 'Номер документа представителя',
            'representative_identity_department_code' => 'Код подразделения документа представителя',
            'representative_identity_issued_by' => 'Кем выдан документ представителя',
            'representative_identity_issued_at' => 'Дата выдачи документа представителя',
            'representative_authority_document_type' => 'Тип документа о полномочиях',
            'representative_authority_document_name' => 'Наименование документа о полномочиях',
            'representative_authority_number' => 'Номер документа о полномочиях',
            'representative_authority_issued_by' => 'Кем выдан документ о полномочиях',
            'representative_authority_issued_at' => 'Дата выдачи документа о полномочиях',
        ];
    }
}
