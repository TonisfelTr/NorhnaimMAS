<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_authed() && group()->doctor_edit;
    }

    public function rules(): array
    {
        return [
            'surname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'patronym' => 'nullable|string|max:255',
            'birth_at' => 'nullable|date',
            'status' => 'nullable|string|max:255',
            'address_job' => 'nullable|string|max:255',
            'clinic_id' => 'required|integer|exists:clinics,id',
            'experience_years' => 'nullable|integer|min:0',
            'experience_months' => 'nullable|integer|min:0|max:11',
        ];
    }

    public function messages(): array
    {
        return [
            'surname.required' => 'Поле "Фамилия" обязательно для заполнения.',
            'name.required' => 'Поле "Имя" обязательно для заполнения.',
            'clinic_id.required' => 'Необходимо выбрать клинику.',
            'clinic_id.exists' => 'Выбранная клиника не существует.',
            'experience_months.max' => 'Опыт работы в месяцах не может превышать 11.',
        ];
    }
}
