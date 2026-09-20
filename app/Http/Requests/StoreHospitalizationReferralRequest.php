<?php

namespace App\Http\Requests;

use App\Models\HospitalizationReferral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHospitalizationReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $destinationRequired = Rule::requiredIf(function (): bool {
            return in_array((string) $this->input('status'), [
                HospitalizationReferral::STATUS_PREPARED,
                HospitalizationReferral::STATUS_SENT,
                HospitalizationReferral::STATUS_ADMITTED,
            ], true);
        });

        return [
            'type' => [
                'required',
                Rule::in([
                    HospitalizationReferral::TYPE_PLANNED,
                    HospitalizationReferral::TYPE_EMERGENCY,
                ]),
            ],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'referral_id' => ['nullable', 'integer', 'min:1'],
            'status' => [
                'required',
                Rule::in([
                    HospitalizationReferral::STATUS_DRAFT,
                    HospitalizationReferral::STATUS_PREPARED,
                    HospitalizationReferral::STATUS_SENT,
                    HospitalizationReferral::STATUS_ADMITTED,
                    HospitalizationReferral::STATUS_CANCELLED,
                ]),
            ],
            'source_mode' => [
                'required',
                Rule::in([
                    HospitalizationReferral::SOURCE_MANUAL,
                    HospitalizationReferral::SOURCE_AI,
                    HospitalizationReferral::SOURCE_COMBINED,
                ]),
            ],

            'destination_organization' => ['nullable', 'string', 'max:255'],
            'destination_address' => [$destinationRequired, 'nullable', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:255'],

            // Это именно предварительный диагноз для текущего направления.
            // Он не меняет установленный диагноз в карточке пациента.
            'diagnosis_code' => [
                'required',
                'string',
                'max:32',
                'regex:/^Z/i',
                Rule::exists('diagnoses', 'code'),
            ],
            'diagnosis_title' => ['nullable', 'string', 'max:255'],
            'clinical_summary' => ['nullable', 'string', 'max:20000'],
            'reason' => ['nullable', 'string', 'max:12000'],

            'planned_at' => ['nullable', 'date'],
            'urgency_level' => [
                'nullable',
                Rule::in(['immediate', 'within_2h', 'within_24h', 'urgent', 'unknown']),
            ],
            'transport_type' => [
                'nullable',
                Rule::in(['ambulance', 'resuscitation', 'medical_transport', 'self', 'accompanied', 'unknown']),
            ],

            'treatment_goal' => ['nullable', 'string', 'max:3000'],
            'expected_duration' => ['nullable', 'string', 'max:255'],
            'prehospital_checklist' => ['nullable', 'string', 'max:5000'],

            'deterioration_started_at' => ['nullable', 'date'],
            'red_flags' => ['nullable', 'string', 'max:5000'],
            'stabilization_measures' => ['nullable', 'string', 'max:5000'],
            'ambulance_required' => ['nullable', 'boolean'],
            'consciousness' => ['nullable', 'string', 'max:255'],
            'blood_pressure' => ['nullable', 'string', 'max:32'],
            'pulse' => ['nullable', 'integer', 'between:0,300'],
            'spo2' => ['nullable', 'integer', 'between:0,100'],
            'temperature' => ['nullable', 'numeric', 'between:30,45'],

            'additional_notes' => ['nullable', 'string', 'max:10000'],
            'ai_analysis' => ['nullable', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_address.required' => 'Укажите адрес стационара, куда направляется пациент.',
            'diagnosis_code.required' => 'Выберите предварительный диагноз из класса Z.',
            'diagnosis_code.regex' => 'Для направления разрешены только диагнозы класса Z.',
            'diagnosis_code.exists' => 'Выбранный диагноз класса Z не найден в справочнике.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $diagnosisCode = trim((string) $this->input('diagnosis_code', ''));

        $this->merge([
            'ambulance_required' => $this->boolean('ambulance_required'),
            'diagnosis_code' => $diagnosisCode !== ''
                ? mb_strtoupper($diagnosisCode)
                : null,
        ]);
    }
}
