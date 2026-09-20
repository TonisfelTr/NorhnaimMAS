<?php

namespace App\Http\Requests;

use App\Models\DriverPsychiatricExamination;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveDriverPsychiatricExaminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ([
            'complaints',
            'disease_history',
            'psychiatric_history',
            'hospitalizations',
            'current_treatment',
            'mental_status',
            'cognitive_behavioral_features',
            'result_rationale',
            'commission_protocol_number',
            'commission_decision',
            'conclusion_text',
            'contraindication_assessment',
        ] as $field) {
            $value = trim((string) $this->input($field, ''));
            $normalized[$field] = $value !== '' ? $value : null;
        }

        $normalized['vehicle_categories'] = collect((array) $this->input('vehicle_categories', []))
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $normalized['risk_flags'] = collect((array) $this->input('risk_flags', []))
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $normalized['medical_record_reviewed'] = $this->boolean('medical_record_reviewed');
        $normalized['commission_refusal'] = $this->boolean('commission_refusal');

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $prepared = $this->input('status') === DriverPsychiatricExamination::STATUS_PREPARED;
        $commissionResult = in_array($this->input('result'), [
            DriverPsychiatricExamination::RESULT_COMMISSION_NO_CONTRAINDICATIONS,
            DriverPsychiatricExamination::RESULT_COMMISSION_CONTRAINDICATIONS,
        ], true);

        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'examination_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(array_keys(DriverPsychiatricExamination::statusOptions()))],
            'purpose' => ['required', Rule::in(array_keys(DriverPsychiatricExamination::purposeOptions()))],
            'contraindication_assessment' => [
                'nullable',
                'string',
                Rule::in(array_keys(DriverPsychiatricExamination::contraindicationAssessmentOptions())),
            ],

            'vehicle_categories' => [Rule::requiredIf($prepared), 'array', 'max:16'],
            'vehicle_categories.*' => [
                'string',
                Rule::in(array_keys(DriverPsychiatricExamination::vehicleCategoryOptions())),
            ],

            'complaints' => ['nullable', 'string', 'max:20000'],
            'disease_history' => ['nullable', 'string', 'max:30000'],
            'psychiatric_history' => ['nullable', 'string', 'max:30000'],
            'hospitalizations' => ['nullable', 'string', 'max:20000'],
            'current_treatment' => ['nullable', 'string', 'max:20000'],
            'mental_status' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:40000'],
            'cognitive_behavioral_features' => ['nullable', 'string', 'max:20000'],

            'risk_flags' => ['array', 'max:6'],
            'risk_flags.*' => [
                'string',
                Rule::in(array_keys(DriverPsychiatricExamination::riskFlagOptions())),
            ],
            'medical_record_reviewed' => [Rule::requiredIf($prepared), 'boolean'],

            'result' => [
                Rule::requiredIf($prepared),
                'nullable',
                Rule::in(array_keys(DriverPsychiatricExamination::resultOptions())),
            ],
            'result_rationale' => ['nullable', 'string', 'max:30000'],

            'commission_protocol_number' => [Rule::requiredIf($prepared && $commissionResult), 'nullable', 'string', 'max:100'],
            'commission_protocol_date' => [Rule::requiredIf($prepared && $commissionResult), 'nullable', 'date', 'before_or_equal:today'],
            'commission_decision' => [Rule::requiredIf($prepared && $commissionResult), 'nullable', 'string', 'max:30000'],
            'commission_refusal' => ['boolean'],
            'conclusion_text' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:30000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $patient = null;
            $patientId = (int) $this->input('patient_id');

            if ($patientId > 0 && !$validator->errors()->has('patient_id')) {
                $patient = Patient::query()->with('diagnose')->find($patientId);
            }

            $diagnosisCode = $patient?->diagnose?->code;
            $contraindicationMatch = DriverPsychiatricExamination::drivingContraindicationMatch($diagnosisCode);
            $assessment = trim((string) $this->input('contraindication_assessment'));
            $result = (string) $this->input('result');

            /*
             * Жёсткий шлюз. Само присутствие F-кода в перечне № 892-р
             * не равно противопоказанию: для раздела I требуется установленный
             * характер течения, для раздела II — статус диспансерного наблюдения.
             * Поэтому при совпадении кода врач обязан явно выполнить клиническую
             * оценку. При подтверждённом противопоказании водительский документ
             * вообще не сохраняется — ни как черновик, ни как оформленный.
             */
            if ($contraindicationMatch !== null && $assessment === '') {
                $validator->errors()->add(
                    'contraindication_assessment',
                    'Диагноз входит в перечень № 892-р. Сначала укажите, подтверждено ли нормативное условие медицинского противопоказания.'
                );
            }

            if ($assessment === DriverPsychiatricExamination::CONTRA_ASSESSMENT_CONFIRMED) {
                $validator->errors()->add(
                    'contraindication_assessment',
                    'Сохранение заблокировано: медицинское противопоказание к управлению транспортными средствами подтверждено.'
                );
            }

            if ($result === DriverPsychiatricExamination::RESULT_COMMISSION_CONTRAINDICATIONS) {
                $validator->errors()->add(
                    'result',
                    'Сохранение водительского документа заблокировано: психиатрическая ВК подтвердила медицинские противопоказания.'
                );
            }

            if (
                $assessment === DriverPsychiatricExamination::CONTRA_ASSESSMENT_COMMISSION_REQUIRED
                && $this->input('status') === DriverPsychiatricExamination::STATUS_PREPARED
                && $result !== DriverPsychiatricExamination::RESULT_REFER_COMMISSION
            ) {
                $validator->errors()->add(
                    'result',
                    'При необходимости решения психиатрической ВК оформленным результатом может быть только направление на ВК.'
                );
            }

            if ($this->input('status') !== DriverPsychiatricExamination::STATUS_PREPARED) {
                return;
            }

            if (!$this->boolean('medical_record_reviewed')) {
                $validator->errors()->add(
                    'medical_record_reviewed',
                    'Перед оформлением подтвердите, что медицинская карта и сведения о психиатрических диагнозах проверены.'
                );
            }

            $riskFlags = collect((array) $this->input('risk_flags', []))->filter()->values();

            /*
             * Если врач сам отметил клинически значимые симптомы/синдромы,
             * нельзя одновременно оформить результат «противопоказания не выявлены»
             * без прохождения сценария психиатрической ВК.
             */
            if (
                $riskFlags->isNotEmpty()
                && $result === DriverPsychiatricExamination::RESULT_NO_CONTRAINDICATIONS
            ) {
                $validator->errors()->add(
                    'result',
                    'При отмеченных настораживающих психиатрических признаках используйте направление на психиатрическую врачебную комиссию.'
                );
            }

            if (
                $result === DriverPsychiatricExamination::RESULT_REFER_COMMISSION
                && trim((string) $this->input('result_rationale')) === ''
            ) {
                $validator->errors()->add(
                    'result_rationale',
                    'Укажите клиническое основание направления на психиатрическую врачебную комиссию.'
                );
            }

            if (
                $result === DriverPsychiatricExamination::RESULT_REFUSED_COMMISSION
                && !$this->boolean('commission_refusal')
            ) {
                $validator->errors()->add(
                    'commission_refusal',
                    'Подтвердите факт отказа пациента от психиатрического освидетельствования врачебной комиссией.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Выберите пациента.',
            'patient_id.exists' => 'Выбранный пациент не найден.',
            'examination_date.required' => 'Укажите дату обследования.',
            'vehicle_categories.required' => 'Для оформленного документа выберите хотя бы одну категорию транспортного средства.',
            'mental_status.required' => 'Для оформленного документа заполните психический статус.',
            'result.required' => 'Для оформленного документа укажите результат обследования.',
            'conclusion_text.required' => 'Для оформленного документа заполните текст заключения.',
            'commission_protocol_number.required' => 'Для решения психиатрической ВК укажите номер протокола.',
            'commission_protocol_date.required' => 'Для решения психиатрической ВК укажите дату протокола.',
            'commission_decision.required' => 'Для решения психиатрической ВК заполните решение комиссии.',
            'contraindication_assessment.in' => 'Выберите допустимый вариант оценки медицинского противопоказания.',
        ];
    }
}
