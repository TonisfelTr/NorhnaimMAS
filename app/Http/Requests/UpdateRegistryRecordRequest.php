<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegistryRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !app()->hasDebugModeEnabled()
            ? (bool)auth()->user()->doctor
            : true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',

            // ФИО
            'surname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'patronym' => 'nullable|string|max:255',

            // Дата рождения и приёма
            'birth_at' => 'required|date',
            'appointment_datetime' => 'required|date|after_or_equal:today',

            // Адреса
            'address_registration' => 'required|string|max:255',
            'address_residence' => 'required|string|max:255',

            // Паспортные данные
            'serial' => 'required|string|max:10',
            'number' => 'required|string|max:10',
            'department_code' => 'required|string|max:20',
            'issued_by' => 'required|string|max:255',
            'issued_at' => 'required|date',
        ];
    }
}
