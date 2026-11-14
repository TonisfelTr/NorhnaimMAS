<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

class PatientProfileUpdateRequest extends FormRequest
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
        $email = trim((string) $this->input('email', ''));
        $phone = trim((string) $this->input('phone', ''));

        $this->merge([
            'email' => $email !== '' ? mb_strtolower($email) : null,
            'phone' => $phone !== '' ? $phone : null,
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
        ];
    }

    public function messages(): array
    {
        return [
            'surname.required' => 'Укажите фамилию.',
            'name.required' => 'Укажите имя.',
            'birth_at.required' => 'Укажите дату рождения.',
            'birth_at.before_or_equal' => 'Дата рождения не может быть в будущем.',
            'gender.required' => 'Укажите пол.',
            'gender.in' => 'Выберите корректное значение пола.',
            'email.email' => 'Укажите корректный адрес электронной почты.',
        ];
    }
}
