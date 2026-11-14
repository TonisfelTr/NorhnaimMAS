<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PatientIdentityLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasRole('patients')
            && ! $user->patient()->exists();
    }

    protected function prepareForValidation(): void
    {
        $digits = static fn ($value): ?string =>
            ($value = preg_replace('/\D+/', '', (string) $value)) !== ''
                ? $value
                : null;

        $this->merge([
            'surname' => trim((string) $this->input('surname')),
            'name' => trim((string) $this->input('name')),
            'patronym' => trim((string) $this->input('patronym')) ?: null,
            'snils' => $digits($this->input('snils')),
            'oms' => ($value = strtoupper(preg_replace(
                '/[^0-9A-Z]/i',
                '',
                (string) $this->input('oms')
            ))) !== '' ? $value : null,
            'document_series' => ($value = strtoupper(preg_replace(
                '/[^0-9A-Z]/i',
                '',
                (string) $this->input('document_series')
            ))) !== '' ? $value : null,
            'document_number' => trim((string) $this->input('document_number')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'patronym' => ['nullable', 'string', 'max:255'],
            'birth_at' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:M,F'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:500'],
            'address_registration' => ['nullable', 'string', 'max:2000'],
            'address_residence' => ['nullable', 'string', 'max:2000'],

            'snils' => [
                'nullable',
                'required_without:document_number',
                'digits:11',
            ],

            'oms' => ['nullable', 'string', 'max:16'],
            'insurance_company' => ['nullable', 'string', 'max:255'],

            'document_type' => [
                'nullable',
                'required_with:document_number',
                'in:passport_rf,birth_certificate,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
            ],
            'document_series' => [
                'nullable',
                'required_if:document_type,passport_rf',
                'string',
                'max:50',
            ],
            'document_number' => [
                'nullable',
                'required_without:snils',
                'string',
                'max:100',
            ],
            'document_issued_by' => ['nullable', 'string', 'max:2000'],
            'document_issued_at' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'document_department_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $snils = (string) $this->input('snils');

            if ($snils !== '' && ! $this->isValidSnils($snils)) {
                $validator->errors()->add(
                    'snils',
                    'Проверьте контрольное число СНИЛС.'
                );
            }

            if (
                $this->filled('document_number')
                && $this->filled('document_number')
                && $this->input('document_type') === 'passport_rf'
                && (
                    ! $this->filled('document_issued_by')
                    || ! $this->filled('document_issued_at')
                )
            ) {
                $validator->errors()->add(
                    'document_issued_by',
                    'Для паспорта РФ укажите кем и когда он выдан.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'surname.required' => 'Укажите фамилию.',
            'name.required' => 'Укажите имя.',
            'birth_at.required' => 'Укажите дату рождения.',
            'birth_at.before_or_equal' => 'Дата рождения не может быть в будущем.',
            'gender.required' => 'Укажите пол.',
            'email.email' => 'Укажите корректный контактный email.',
            'snils.required_without' => 'Укажите СНИЛС или данные удостоверяющего документа.',
            'snils.digits' => 'СНИЛС должен содержать 11 цифр.',
            'document_number.required_without' => 'Укажите номер документа или СНИЛС.',
            'document_type.required_with' => 'Выберите тип удостоверяющего документа.',
            'document_series.required_if' => 'Для паспорта РФ укажите серию.',
            'document_issued_at.before_or_equal' => 'Дата выдачи документа не может быть в будущем.',
        ];
    }

    private function isValidSnils(string $snils): bool
    {
        if (! preg_match('/^\d{11}$/', $snils)) {
            return false;
        }

        $number = substr($snils, 0, 9);
        $control = (int) substr($snils, 9, 2);
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $number[$i]) * (9 - $i);
        }

        if ($sum < 100) {
            $expected = $sum;
        } elseif ($sum === 100 || $sum === 101) {
            $expected = 0;
        } else {
            $expected = $sum % 101;

            if ($expected === 100) {
                $expected = 0;
            }
        }

        return $control === $expected;
    }
}
