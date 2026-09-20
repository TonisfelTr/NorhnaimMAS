<?php

namespace App\Http\Requests;

use App\Models\MilitaryCommissionDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMilitaryCommissionDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableStrings = [
            'requesting_organization',
            'request_number',
            'purpose',
            'complaints',
            'anamnesis',
            'objective_status',
            'examination_results',
            'treatment_history',
            'medical_conclusion',
            'recommendations',
            'notes',
        ];

        $normalized = [];

        foreach ($nullableStrings as $field) {
            $value = trim((string) $this->input($field, ''));
            $normalized[$field] = $value !== '' ? $value : null;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'document_date' => ['required', 'date', 'before_or_equal:today'],
            'document_type' => [
                'required',
                'string',
                Rule::in(array_keys(MilitaryCommissionDocument::documentTypeOptions())),
            ],
            'status' => [
                'required',
                'string',
                Rule::in(array_keys(MilitaryCommissionDocument::statusOptions())),
            ],
            'requesting_organization' => ['nullable', 'string', 'max:255'],
            'request_number' => ['nullable', 'string', 'max:100'],
            'request_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purpose' => ['nullable', 'string', 'max:5000'],
            'complaints' => ['nullable', 'string', 'max:20000'],
            'anamnesis' => ['nullable', 'string', 'max:30000'],
            'objective_status' => ['nullable', 'string', 'max:30000'],
            'examination_results' => ['nullable', 'string', 'max:30000'],
            'treatment_history' => ['nullable', 'string', 'max:30000'],
            'medical_conclusion' => [
                Rule::requiredIf($this->input('status') === MilitaryCommissionDocument::STATUS_PREPARED),
                'nullable',
                'string',
                'max:30000',
            ],
            'recommendations' => ['nullable', 'string', 'max:20000'],
            'notes' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Выберите пациента.',
            'patient_id.exists' => 'Выбранный пациент не найден.',
            'document_date.before_or_equal' => 'Дата документа не может быть позже текущей даты.',
            'request_date.before_or_equal' => 'Дата запроса не может быть позже текущей даты.',
            'medical_conclusion.required' => 'Для статуса «Документ оформлен» заполните медицинское заключение.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'document_date' => 'дата документа',
            'document_type' => 'вид документа',
            'status' => 'статус',
            'requesting_organization' => 'организация-получатель',
            'request_number' => 'номер запроса',
            'request_date' => 'дата запроса',
            'purpose' => 'основание и цель',
            'complaints' => 'жалобы',
            'anamnesis' => 'анамнез',
            'objective_status' => 'объективный статус',
            'examination_results' => 'результаты обследований',
            'treatment_history' => 'сведения о лечении',
            'medical_conclusion' => 'медицинское заключение',
            'recommendations' => 'рекомендации',
            'notes' => 'примечания',
        ];
    }
}
