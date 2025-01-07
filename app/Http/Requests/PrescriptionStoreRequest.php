<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_authed() && in_array(auth()->user()->getUserType(), ['Администрация', 'Доктор']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|string', // Поле для выбора пациента
            'drug_id' => 'required|string', // Поле для выбора лекарства
            'drug_form' => 'required|string', // Форма препарата
            'dosage' => 'required|numeric|min:1', // Дозировка
            'quantity' => 'required|numeric|min:1', // Количество лекарственной формы
            'standard' => 'required|numeric|min:1', // Количество стандартов
            'taking_drug' => 'required|numeric|min:1|max:4', // Количество приёмов препарата
            'taking_count' => 'required|numeric|min:1|max:5', // Количество раз в день
            'taking_time_meal' => 'required|in:1,2', // Время приёма относительно еды
            'usage_instructions' => 'required|string|max:500', // Схема приёма
        ];
    }

    /**
     * Custom validation messages for this request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Поле "Пациент" обязательно.',
            'drug_id.required' => 'Поле "Лекарство" обязательно.',
            'drug_form.required' => 'Поле "Форма препарата" обязательно.',
            'dosage.required' => 'Поле "Дозировка" обязательно.',
            'dosage.numeric' => 'Дозировка должна быть числом.',
            'quantity.required' => 'Поле "Количество лекарственной формы" обязательно.',
            'quantity.numeric' => 'Количество лекарственной формы должно быть числом.',
            'standard.required' => 'Поле "Количество стандартов" обязательно.',
            'standard.numeric' => 'Количество стандартов должно быть числом.',
            'standard.min' => 'Минимальное значение для стандартов — 1.',
            'taking_drug.required' => 'Поле "Количество приёмов препарата" обязательно.',
            'taking_drug.numeric' => 'Количество приёмов препарата должно быть числом.',
            'taking_drug.min' => 'Минимальное количество приёмов — 1.',
            'taking_drug.max' => 'Максимальное количество приёмов — 4.',
            'taking_count.required' => 'Поле "Количество раз в день" обязательно.',
            'taking_count.numeric' => 'Количество раз в день должно быть числом.',
            'taking_count.min' => 'Минимальное количество раз в день — 1.',
            'taking_count.max' => 'Максимальное количество раз в день — 5.',
            'taking_time_meal.required' => 'Поле "Время приёма относительно еды" обязательно.',
            'taking_time_meal.in' => 'Недопустимое значение для времени приёма.',
            'usage_instructions.required' => 'Поле "Схема приёма" обязательно.',
            'usage_instructions.max' => 'Схема приёма не должна превышать 500 символов.',
        ];
    }
}
