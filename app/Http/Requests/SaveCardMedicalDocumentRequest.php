<?php

namespace App\Http\Requests;

use App\Support\CardDocuments\DocumentCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveCardMedicalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Объектный доступ проверяет контроллер до чтения/записи содержимого карты.
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_diagnosis' => $this->boolean('include_diagnosis'),
            'source_options' => $this->input('source_options', []),
        ]);
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $config = is_string($type) ? (DocumentCatalog::TYPES[$type] ?? null) : null;
        $fields = $config ? array_keys($config['fields']) : [];
        $rules = [
            'type' => ['required', Rule::in(array_keys(DocumentCatalog::TYPES))],
            'intent' => ['required', Rule::in(['draft', 'final'])],
            'version' => ['sometimes', 'integer', 'min:1'],
            'issued_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'organization' => ['nullable', 'required_if:intent,final', 'string', 'max:255'],
            'organization_details' => ['nullable', 'string', 'max:1000'],
            'author_position' => ['nullable', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:500'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'include_diagnosis' => ['required', 'boolean'],
            'diagnosis_code' => ['nullable', 'string', 'max:64'],
            'diagnosis_title' => ['nullable', 'string', 'max:2000'],
            'period_mode' => ['required', Rule::in(['all', 'range'])],
            'period_from' => ['nullable', 'required_if:period_mode,range', 'date_format:Y-m-d'],
            'period_to' => ['nullable', 'required_if:period_mode,range', 'date_format:Y-m-d',
                'after_or_equal:period_from', 'before_or_equal:issued_at'],
            'source_options' => ['array', 'max:' . count(DocumentCatalog::SOURCES)],
            'source_options.*' => ['string', 'distinct', Rule::in(array_keys(DocumentCatalog::SOURCES))],
            'fields' => ['required', 'array' . ($fields ? ':' . implode(',', $fields) : '')],
            'reviewed' => ['accepted_if:intent,final'],
        ];

        foreach ($fields as $field) {
            $rules['fields.' . $field] = ['nullable', 'string', 'max:30000'];
            if (in_array($field, $config['required'], true)) {
                $rules['fields.' . $field][] = 'required_if:intent,final';
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($this->input('intent') !== 'final') {
                return;
            }

            // Пробельные поля не считаются заполненным заключением.
            $config = DocumentCatalog::get($this->string('type')->toString());
            foreach ($config['required'] as $field) {
                if (trim((string) $this->input('fields.' . $field)) === '') {
                    $validator->errors()->add('fields.' . $field,
                        'Заполните поле «' . $config['fields'][$field] . '».');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно.',
            'required_if' => 'Заполните поле «:attribute» для выбранного действия.',
            'accepted_if' => 'Перед оформлением подтвердите проверку документа.',
            'date_format' => 'Укажите корректную дату в поле «:attribute».',
            'before_or_equal' => 'Дата «:attribute» не должна быть позже :date.',
            'after_or_equal' => 'Конец периода не может быть раньше его начала.',
            'max' => 'Превышен допустимый размер поля «:attribute».',
            'in' => 'Недопустимое значение поля «:attribute».',
            'array' => 'Неверный состав поля «:attribute».',
        ];
    }

    public function attributes(): array
    {
        $attributes = [
            'organization' => 'Организация', 'issued_at' => 'Дата документа',
            'period_from' => 'Начало периода', 'period_to' => 'Конец периода',
            'reviewed' => 'Проверка документа', 'fields' => 'Текст документа',
        ];
        foreach (DocumentCatalog::TYPES as $config) {
            foreach ($config['fields'] as $key => $label) {
                $attributes['fields.' . $key] = $label;
            }
        }

        return $attributes;
    }
}
