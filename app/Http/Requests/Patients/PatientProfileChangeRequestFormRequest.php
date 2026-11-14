<?php

namespace App\Http\Requests\Patients;

use App\Models\PatientClinic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PatientProfileChangeRequestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasRole('patients')
            && $user->patient()->exists();
    }

    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D+/', '', (string) $this->input('snils'));
        $oms = strtoupper(preg_replace('/[^0-9A-Z]/i', '', (string) $this->input('oms')));

        $reviewRoute = trim((string) $this->input('review_route'));
        $reviewTarget = null;
        $reviewClinicId = null;

        if (preg_match('/\A(doctor|administration):(\d+)\z/', $reviewRoute, $matches)) {
            $reviewTarget = $matches[1];
            $reviewClinicId = (int) $matches[2];
        }

        $this->merge([
            'email' => ($email = mb_strtolower(trim((string) $this->input('email')))) !== '' ? $email : null,
            'phone' => ($phone = trim((string) $this->input('phone'))) !== '' ? $phone : null,
            'snils' => $digits !== '' ? $digits : null,
            'oms' => $oms !== '' ? $oms : null,
            'document_series' => ($series = trim((string) $this->input('document_series'))) !== '' ? $series : null,
            'document_number' => ($number = trim((string) $this->input('document_number'))) !== '' ? $number : null,
            'review_target' => $reviewTarget,
            'review_clinic_id' => $reviewClinicId,
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
            'snils' => ['nullable', 'digits:11'],
            'oms' => ['nullable', 'string', 'max:16'],
            'insurance_company' => ['nullable', 'string', 'max:255'],
            'document_type' => [
                'nullable',
                'required_with:document_number',
                'in:passport_rf,birth_certificate,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
            ],
            'document_series' => ['nullable', 'string', 'max:50'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_issued_by' => ['nullable', 'string', 'max:2000'],
            'document_issued_at' => ['nullable', 'date', 'before_or_equal:today'],
            'document_department_code' => ['nullable', 'string', 'max:20'],
            'review_route' => ['required', 'string', 'regex:/\A(?:doctor|administration):\d+\z/'],
            'review_target' => ['required', 'in:doctor,administration'],
            'review_clinic_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $snils = (string) $this->input('snils');

            if ($snils !== '' && ! $this->isValidSnils($snils)) {
                $validator->errors()->add('snils', 'Проверьте контрольное число СНИЛС.');
            }

            if (
                $this->filled('document_type')
                && ! $this->filled('document_number')
            ) {
                $validator->errors()->add(
                    'document_number',
                    'Укажите номер удостоверяющего документа.'
                );
            }

            if (
                $this->filled('document_number')
                && ! $this->filled('document_type')
            ) {
                $validator->errors()->add(
                    'document_type',
                    'Выберите тип удостоверяющего документа.'
                );
            }

            if (
                $this->filled('document_number')
                && $this->input('document_type') === 'passport_rf'
                && (
                    ! $this->filled('document_series')
                    || ! $this->filled('document_issued_by')
                    || ! $this->filled('document_issued_at')
                )
            ) {
                $validator->errors()->add(
                    'document_issued_by',
                    'Для паспорта РФ укажите серию, кем и когда он выдан.'
                );
            }

            $patient = $this->user()?->patient;

            if (!$patient) {
                return;
            }

            $membership = PatientClinic::query()
                ->where('patient_id', (int) $patient->id)
                ->where('clinic_id', (int) $this->input('review_clinic_id'))
                ->where('is_active', true)
                ->first();

            if (!$membership) {
                $validator->errors()->add(
                    'review_route',
                    'Выбранная клиника не связана с вашей медицинской картой.'
                );

                return;
            }

            if (
                $this->input('review_target') === 'doctor'
                && !$membership->doctor_id
            ) {
                $validator->errors()->add(
                    'review_route',
                    'В выбранной клинике лечащий врач не назначен. Направьте запрос администрации клиники.'
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
            'snils.digits' => 'СНИЛС должен содержать 11 цифр.',
            'review_route.required' => 'Выберите клинику и получателя запроса.',
            'review_route.regex' => 'Выберите корректный маршрут согласования.',
            'review_target.required' => 'Выберите, кому направить запрос.',
            'review_clinic_id.required' => 'Выберите клинику.',
            'reason.required' => 'Укажите причину изменения.',
            'reason.min' => 'Причина должна содержать не менее 5 символов.',
            'email.email' => 'Укажите корректный контактный email.',
        ];
    }

    private function isValidSnils(string $snils): bool
    {
        if (! preg_match('/^\d{11}$/', $snils)) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $snils[$i]) * (9 - $i);
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

        return (int) substr($snils, 9, 2) === $expected;
    }
}
