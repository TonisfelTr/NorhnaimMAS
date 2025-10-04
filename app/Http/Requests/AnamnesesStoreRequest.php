<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnamnesesStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() &&
            (
                !config('app.debug')
                    ? auth()->user()->hasRole('doctor')
                    : auth()->user()->hasRole('doctor') || auth()->user()->hasRole('admins')
            );
    }

    /**
     * Перед валидацией нормализуем вход:
     * - определим источник (ai|manual), если не прислали
     * - декодируем JSON со списками симптомов в массивы
     * - приведём булево
     */
    protected function prepareForValidation(): void
    {
        $source = $this->input('diag_source');

        if (!$source) {
            // Попробуем угадать по наличию полей
            if ($this->filled('ai_diagnosis_code') || $this->filled('ai_symptoms')) {
                $source = 'ai';
            } else {
                $source = 'manual';
            }
        }

        if ($source == 'ai') {
            $aiSymptoms = $this->input('symptoms_json');
            if (is_string($aiSymptoms)) {
                $decoded = json_decode($aiSymptoms, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $aiSymptoms = $decoded;
                }
            }
        } elseif ($source == 'manual') {
            $manualSymptoms = $this->input('symptoms_json');
            if (is_string($manualSymptoms)) {
                $decoded = json_decode($manualSymptoms, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $manualSymptoms = $decoded;
                }
            }
        }

        $this->merge([
            'source'          => $source,
            'ai_symptoms'     => is_array($aiSymptoms ?? '') ? $aiSymptoms : [],
            'manual_symptoms' => is_array($manualSymptoms ?? '') ? $manualSymptoms : [],
            'assign_current'  => (bool) $this->boolean('assign_current'),
        ]);
    }

    public function rules(): array
    {
//        dd($this->all());
        $source = $this->input('diag_source', 'manual');

        $base = [
            'diag_source'         => 'required|in:ai,manual',
            'title'           => 'required|string|max:255',
            'text'           => 'required|string|min:30',     // можно поменять порог под себя
            'category'       => 'nullable|string|max:50',     // либо in:primary,dynamics,clarification — если у тебя фикс-список
            'created_at'     => 'nullable|date',
            'assign_current' => 'boolean',
        ];

        $ai = [
            'ai_diagnosis_code'  => 'nullable|string|max:20',
            'ai_diagnosis_title' => 'nullable|string|max:255',
            'ai_symptoms'        => 'array',
            'ai_symptoms.*'      => 'string|max:200',
        ];

        $manual = [
            'manual_diagnosis_code'  => 'nullable|string|max:20',
            'manual_diagnosis_title' => 'nullable|string|max:255',
            'manual_symptoms'        => 'array',
            'manual_symptoms.*'      => 'string|max:200',
        ];

        if ($this->boolean('assign_current')) {
            if ($source === 'ai') {
                $ai['ai_diagnosis_code'] .= '|required';
            } else {
                $manual['manual_diagnosis_code'] .= '|required';
            }
        }

        return array_merge(
            $base,
            $source === 'ai' ? $ai : $manual
        );
    }

    public function messages(): array
    {
        return [
            'source.required' => 'Не указан источник данных (ИИ или вручную).',
            'text.min'        => 'Текст записи слишком короткий.',
            'ai_diagnosis_code.required'     => 'Укажите код диагноза, если назначаете его текущим.',
            'manual_diagnosis_code.required' => 'Укажите код диагноза, если назначаете его текущим.',
        ];
    }

    /**
     * Нормализованный payload, с которым удобно работать в контроллере:
     *  - diagnosis_code / diagnosis_title — уже «склеены» из нужного таба
     *  - symptoms — массив строк
     */
    public function payload(): array
    {
        $source = $this->input('source');

        $diagnosisCode  = $source === 'ai'
            ? $this->input('ai_diagnosis_code')
            : $this->input('manual_diagnosis_code');

        $diagnosisTitle = $source === 'ai'
            ? $this->input('ai_diagnosis_title')
            : $this->input('manual_diagnosis_title');

        $symptoms = $source === 'ai'
            ? $this->input('ai_symptoms', [])
            : $this->input('manual_symptoms', []);

        return [
            'source'          => $source,                 // 'ai' | 'manual'
            'title'           => $this->input('title'),
            'text'            => $this->input('text'),
            'category'        => $this->input('category'),
            'created_at'      => $this->input('created_at'),
            'assign_current'  => $this->boolean('assign_current'),
            'diagnosis_code'  => $diagnosisCode,
            'diagnosis_title' => $diagnosisTitle,
            'symptoms'        => array_values(array_filter(array_map('strval', $symptoms))),
        ];
    }
}
