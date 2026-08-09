<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabResearchRequest extends FormRequest
{
    protected $errorBag = 'labOrder';

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->doctor()->exists();
    }

    public function rules(): array
    {
        return [
            'planned_at' => ['required', 'date'],
            'priority' => ['required', Rule::in(['normal', 'urgent'])],
            'laboratory' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:5000'],
            'param_ids' => ['required', 'array', 'min:1'],
            'param_ids.*' => ['required', 'integer', 'distinct', 'exists:lab_parameters,id'],
            'sampleType' => ['required', Rule::in(['моча', 'кровь', 'кровь/моча', 'плазма', 'расчёт', 'сыворотка'])],
            'save' => ['nullable'],
            'save_and_new' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'planned_at.required' => 'Укажите плановую дату забора материала.',
            'planned_at.date' => 'Некорректно указана плановая дата.',
            'priority.required' => 'Укажите приоритет исследования.',
            'priority.in' => 'Выбран некорректный приоритет.',
            'laboratory.max' => 'Название лаборатории не должно превышать 255 символов.',
            'comment.max' => 'Комментарий не должен превышать 5000 символов.',
            'param_ids.required' => 'Выберите хотя бы один лабораторный показатель.',
            'param_ids.min' => 'Выберите хотя бы один лабораторный показатель.',
            'param_ids.*.distinct' => 'Один лабораторный показатель выбран несколько раз.',
            'param_ids.*.exists' => 'Один из выбранных лабораторных показателей не найден.',
            'sampleType.required' => 'Выберите биоматериал.',
            'sampleType.in' => 'Выбран некорректный тип биоматериала.',
        ];
    }
}
