<?php

namespace App\Http\Requests;

use App\Models\MseReferral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveMseReferralRequest extends FormRequest
{
    /**
     * Цели, которые означают первичное установление инвалидности.
     * Для них действует контроль минимального срока наблюдения.
     */
    private const DISABILITY_ESTABLISHMENT_PURPOSES = [
        'disability_group',
        'disabled_child',
    ];

    private const NULLABLE_STRINGS = [
        'protocol_number',
        'organization_name',
        'organization_address',
        'organization_ogrn',
        'bureau_name',
        'patient_citizenship_country',
        'patient_address',
        'patient_location_name',
        'patient_location_address',
        'patient_location_ogrn',
        'patient_phone',
        'patient_email',
        'patient_snils',
        'patient_oms',
        'patient_identity_name',
        'patient_identity_series',
        'patient_identity_number',
        'patient_identity_issued_by',
        'representative_full_name',
        'representative_authority_document',
        'representative_identity',
        'representative_phone',
        'representative_email',
        'representative_snils',
        'guardian_organization_name',
        'guardian_organization_address',
        'guardian_organization_ogrn',
        'previous_disability_cause_other',
        'previous_professional_loss_term',
        'previous_professional_loss_other',
        'education_organization',
        'education_class',
        'education_level',
        'education_profession',
        'employment_profession',
        'employment_qualification',
        'employment_experience',
        'employment_current_work',
        'employment_conditions',
        'employment_organization',
        'employment_address',
        'disease_history',
        'life_history',
        'eln_number',
        'ipra_number',
        'ipra_protocol_number',
        'rehab_results_notes',
        'body_build',
        'physical_development',
        'health_status',
        'complaints',
        'complications',
        'concomitant_diseases',
        'concomitant_codes',
        'medical_rehabilitation',
        'medications_devices',
        'reconstructive_surgery',
        'prosthetics_orthotics',
        'sanatorium_treatment',
        'special_medical_care',
        'additional_notes',
        'commission_chairman',
    ];

    private const BOOLEAN_FIELDS = [
        'requires_home_examination',
        'palliative_care',
        'primary_prosthetics',
        'patient_homeless',
        'has_eln',
        'remove_consent_document',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (self::NULLABLE_STRINGS as $field) {
            $value = trim((string) $this->input($field, ''));
            $normalized[$field] = $value !== '' ? $value : null;
        }

        foreach (self::BOOLEAN_FIELDS as $field) {
            $normalized[$field] = $this->boolean($field);
        }

        $normalized['purposes'] = collect((array) $this->input('purposes', []))
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $normalized['commission_members'] = collect((array) $this->input('commission_members', []))
            ->map(static fn (mixed $value): ?string => ($value = trim((string) $value)) !== '' ? $value : null)
            ->filter()
            ->values()
            ->all();

        $normalized['temporary_disability'] = $this->normalizeRows(
            (array) $this->input('temporary_disability', []),
            ['start_date', 'end_date', 'duration', 'diagnosis']
        );

        $normalized['examinations'] = $this->normalizeRows(
            (array) $this->input('examinations', []),
            ['date', 'code', 'name', 'result']
        );

        foreach ([
            'height_cm',
            'weight_kg',
            'bmi',
            'daily_excretions_ml',
            'waist_cm',
            'hip_cm',
            'birth_weight_g',
            'previous_professional_loss_percent',
        ] as $numberField) {
            if ($this->input($numberField) === '') {
                $normalized[$numberField] = null;
            }
        }

        $observedSinceDate = trim((string) $this->input('observed_since_date', ''));
        $normalized['observed_since_date'] = $observedSinceDate !== ''
            ? $observedSinceDate
            : null;

        /*
         * В печатной форме 088/у нужен именно год.
         * Его формируем на сервере из точной даты, не доверяя hidden-полю.
         */
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $observedSinceDate, $matches) === 1) {
            $normalized['observed_since_year'] = (int) $matches[1];
        } elseif ($this->input('observed_since_year') === '') {
            $normalized['observed_since_year'] = null;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $prepared = $this->input('status') === MseReferral::STATUS_PREPARED;
        $currentYear = (int) now()->format('Y');
        $routeDocument = $this->route('mseReferral');
        $hasExistingConsentDocument = $routeDocument instanceof MseReferral
            && $routeDocument->hasConsentDocument();
        $removingConsentDocument = $this->boolean('remove_consent_document');
        $consentDocumentRequired = $prepared
            && (!$hasExistingConsentDocument || $removingConsentDocument);
        $minimumObservationPeriodRequired = $this->minimumObservationPeriodRequired();

        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'document_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'string', Rule::in(array_keys(MseReferral::statusOptions()))],
            'protocol_number' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:100'],
            'protocol_date' => [Rule::requiredIf($prepared), 'nullable', 'date', 'before_or_equal:today'],
            'referral_kind' => ['required', 'string', Rule::in(array_keys(MseReferral::referralKindOptions()))],
            'consent_date' => [Rule::requiredIf($prepared), 'nullable', 'date', 'before_or_equal:today'],
            'remove_consent_document' => ['boolean'],
            'consent_document' => [
                Rule::requiredIf($consentDocumentRequired),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:15360',
            ],
            'examination_format' => ['required', 'string', Rule::in(array_keys(MseReferral::examinationFormatOptions()))],
            'notification_method' => ['nullable', 'string', Rule::in(array_keys(MseReferral::notificationMethodOptions()))],
            'requires_home_examination' => ['boolean'],
            'palliative_care' => ['boolean'],
            'primary_prosthetics' => ['boolean'],
            'purposes' => [Rule::requiredIf($prepared), 'array', 'max:11'],
            'purposes.*' => ['string', Rule::in(array_keys(MseReferral::purposeOptions()))],

            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_address' => ['nullable', 'string', 'max:1000'],
            'organization_ogrn' => ['nullable', 'string', 'max:30'],
            'bureau_name' => ['nullable', 'string', 'max:255'],

            'patient_sex' => ['nullable', 'string', Rule::in(array_keys(MseReferral::sexOptions()))],
            'patient_citizenship' => ['nullable', 'string', Rule::in(array_keys(MseReferral::citizenshipOptions()))],
            'patient_citizenship_country' => [
                Rule::requiredIf($this->input('patient_citizenship') === 'foreign'),
                'nullable',
                'string',
                'max:120',
            ],
            'patient_military_duty' => ['nullable', 'string', Rule::in(array_keys(MseReferral::militaryDutyOptions()))],
            'patient_address' => ['nullable', 'string', 'max:1000'],
            'patient_homeless' => ['boolean'],
            'patient_location_type' => ['nullable', 'string', Rule::in(array_keys(MseReferral::locationTypeOptions()))],
            'patient_location_name' => ['nullable', 'string', 'max:255'],
            'patient_location_address' => ['nullable', 'string', 'max:1000'],
            'patient_location_ogrn' => ['nullable', 'string', 'max:30'],
            'patient_phone' => ['nullable', 'string', 'max:80'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'patient_snils' => ['nullable', 'string', 'max:30'],
            'patient_oms' => ['nullable', 'string', 'max:80'],
            'patient_identity_name' => ['nullable', 'string', 'max:120'],
            'patient_identity_series' => ['nullable', 'string', 'max:30'],
            'patient_identity_number' => ['nullable', 'string', 'max:50'],
            'patient_identity_issued_by' => ['nullable', 'string', 'max:1000'],
            'patient_identity_issue_date' => ['nullable', 'date', 'before_or_equal:today'],

            'representative_full_name' => ['nullable', 'string', 'max:255'],
            'representative_birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'representative_authority_document' => ['nullable', 'string', 'max:1000'],
            'representative_identity' => ['nullable', 'string', 'max:1000'],
            'representative_phone' => ['nullable', 'string', 'max:80'],
            'representative_email' => ['nullable', 'email', 'max:255'],
            'representative_snils' => ['nullable', 'string', 'max:30'],
            'guardian_organization_name' => ['nullable', 'string', 'max:255'],
            'guardian_organization_address' => ['nullable', 'string', 'max:1000'],
            'guardian_organization_ogrn' => ['nullable', 'string', 'max:30'],

            'previous_disability_group' => ['nullable', 'string', Rule::in(array_keys(MseReferral::disabilityGroupOptions()))],
            'previous_disability_until' => ['nullable', 'date'],
            'previous_disability_period' => ['nullable', 'string', Rule::in(array_keys(MseReferral::disabilityPeriodOptions()))],
            'previous_disability_cause' => ['nullable', 'string', Rule::in(array_keys(MseReferral::disabilityCauseOptions()))],
            'previous_disability_cause_other' => ['nullable', 'string', 'max:1000'],
            'previous_professional_loss_percent' => ['nullable', 'numeric', 'between:0,100'],
            'previous_professional_loss_term' => ['nullable', 'string', 'max:255'],
            'previous_professional_loss_until' => ['nullable', 'date'],
            'previous_professional_loss_other' => ['nullable', 'string', 'max:3000'],

            'education_organization' => ['nullable', 'string', 'max:1000'],
            'education_class' => ['nullable', 'string', 'max:120'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'education_profession' => ['nullable', 'string', 'max:1000'],
            'employment_profession' => ['nullable', 'string', 'max:255'],
            'employment_qualification' => ['nullable', 'string', 'max:255'],
            'employment_experience' => ['nullable', 'string', 'max:255'],
            'employment_current_work' => ['nullable', 'string', 'max:3000'],
            'employment_conditions' => ['nullable', 'string', 'max:3000'],
            'employment_organization' => ['nullable', 'string', 'max:1000'],
            'employment_address' => ['nullable', 'string', 'max:1000'],

            'observed_since_date' => [
                Rule::requiredIf($prepared || $minimumObservationPeriodRequired),
                'nullable',
                'date',
                'before_or_equal:document_date',
            ],
            'observed_since_year' => ['nullable', 'integer', 'between:1900,' . $currentYear],
            'disease_history' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:50000'],
            'life_history' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:50000'],
            'temporary_disability' => ['array', 'max:30'],
            'temporary_disability.*.start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'temporary_disability.*.end_date' => ['nullable', 'date', 'before_or_equal:today'],
            'temporary_disability.*.duration' => ['nullable', 'string', 'max:120'],
            'temporary_disability.*.diagnosis' => ['nullable', 'string', 'max:1000'],
            'has_eln' => ['boolean'],
            'eln_number' => ['nullable', 'string', 'max:100'],
            'ipra_number' => ['nullable', 'string', 'max:100'],
            'ipra_protocol_number' => ['nullable', 'string', 'max:100'],
            'ipra_protocol_date' => ['nullable', 'date'],
            'rehab_function_restoration' => ['nullable', 'string', Rule::in(array_keys(MseReferral::rehabilitationEffectOptions()))],
            'rehab_function_compensation' => ['nullable', 'string', Rule::in(array_keys(MseReferral::rehabilitationEffectOptions()))],
            'rehab_results_notes' => ['nullable', 'string', 'max:10000'],

            'height_cm' => ['nullable', 'numeric', 'between:20,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:0.5,500'],
            'bmi' => ['nullable', 'numeric', 'between:1,150'],
            'body_build' => ['nullable', 'string', 'max:255'],
            'daily_excretions_ml' => ['nullable', 'integer', 'between:0,20000'],
            'waist_cm' => ['nullable', 'numeric', 'between:10,300'],
            'hip_cm' => ['nullable', 'numeric', 'between:10,300'],
            'birth_weight_g' => ['nullable', 'numeric', 'between:100,10000'],
            'physical_development' => ['nullable', 'string', 'max:1000'],
            'health_status' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:50000'],
            'complaints' => [Rule::requiredIf($prepared), 'nullable', 'string', 'max:30000'],
            'examinations' => ['array', 'max:100'],
            'examinations.*.date' => ['nullable', 'date', 'before_or_equal:today'],
            'examinations.*.code' => ['nullable', 'string', 'max:100'],
            'examinations.*.name' => ['nullable', 'string', 'max:1000'],
            'examinations.*.result' => ['nullable', 'string', 'max:10000'],
            'complications' => ['nullable', 'string', 'max:30000'],
            'concomitant_diseases' => ['nullable', 'string', 'max:30000'],
            'concomitant_codes' => ['nullable', 'string', 'max:1000'],

            'clinical_prognosis' => ['nullable', 'string', Rule::in(array_keys(MseReferral::prognosisOptions()))],
            'rehabilitation_potential' => ['nullable', 'string', Rule::in(array_keys(MseReferral::rehabilitationPotentialOptions()))],
            'rehabilitation_prognosis' => ['nullable', 'string', Rule::in(array_keys(MseReferral::prognosisOptions()))],
            'medical_rehabilitation' => ['nullable', 'string', 'max:30000'],
            'medications_devices' => ['nullable', 'string', 'max:30000'],
            'reconstructive_surgery' => ['nullable', 'string', 'max:30000'],
            'prosthetics_orthotics' => ['nullable', 'string', 'max:30000'],
            'sanatorium_treatment' => ['nullable', 'string', 'max:30000'],
            'special_medical_care' => ['nullable', 'string', 'max:30000'],
            'additional_notes' => ['nullable', 'string', 'max:30000'],
            'commission_chairman' => ['nullable', 'string', 'max:255'],
            'commission_members' => ['array', 'max:10'],
            'commission_members.*' => ['string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (!$this->minimumObservationPeriodRequired()) {
                return;
            }

            if (
                $validator->errors()->has('observed_since_date')
                || $validator->errors()->has('document_date')
            ) {
                return;
            }

            $observedSinceDate = $this->input('observed_since_date');
            $documentDate = $this->input('document_date');

            if (!$observedSinceDate || !$documentDate) {
                return;
            }

            try {
                $observedSince = Carbon::parse($observedSinceDate)->startOfDay();
                $referralDate = Carbon::parse($documentDate)->startOfDay();
                $eligibleFrom = $observedSince->copy()->addYearNoOverflow();
            } catch (\Throwable) {
                return;
            }

            if ($eligibleFrom->greaterThan($referralDate)) {
                $validator->errors()->add(
                    'observed_since_date',
                    'Для первичного направления на установление инвалидности пациент должен наблюдаться не менее одного года. '
                    . 'Направление можно оформить не ранее '
                    . $eligibleFrom->format('d.m.Y')
                    . '.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Выберите пациента.',
            'patient_id.exists' => 'Выбранный пациент не найден.',
            'document_date.before_or_equal' => 'Дата направления не может быть позже текущей даты.',
            'protocol_number.required' => 'Для оформленного направления укажите номер протокола врачебной комиссии.',
            'protocol_date.required' => 'Для оформленного направления укажите дату протокола врачебной комиссии.',
            'protocol_date.before_or_equal' => 'Дата протокола не может быть позже текущей даты.',
            'consent_date.required' => 'Для оформленного направления укажите дату письменного согласия.',
            'consent_date.before_or_equal' => 'Дата согласия не может быть позже текущей даты.',
            'consent_document.required' => 'Для оформленного направления загрузите подписанное разрешение на проведение МСЭ.',
            'consent_document.uploaded' => 'Не удалось загрузить файл разрешения. Проверьте размер файла и серверные ограничения.',
            'consent_document.file' => 'Не удалось распознать загруженный документ.',
            'consent_document.mimes' => 'Разрешение можно загрузить в формате PDF, JPG, PNG или WEBP.',
            'consent_document.max' => 'Размер файла разрешения не должен превышать 15 МБ.',
            'purposes.required' => 'Для оформленного направления выберите хотя бы одну цель МСЭ.',
            'observed_since_date.required' => 'Для первичного направления на установление инвалидности укажите точную дату начала наблюдения пациента.',
            'observed_since_date.date' => 'Проверьте дату начала наблюдения пациента.',
            'observed_since_date.before_or_equal' => 'Дата начала наблюдения не может быть позже даты направления.',
            'disease_history.required' => 'Для оформленного направления заполните анамнез заболевания.',
            'life_history.required' => 'Для оформленного направления заполните анамнез жизни.',
            'complaints.required' => 'Для оформленного направления укажите жалобы пациента или сведения со слов законного представителя/родственников.',
            'health_status.required' => 'Для оформленного направления заполните психический статус и стойкие нарушения психических функций.',
            'patient_citizenship_country.required' => 'Укажите иностранное государство.',
            'patient_email.email' => 'Проверьте адрес электронной почты пациента.',
            'representative_email.email' => 'Проверьте адрес электронной почты представителя.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'document_date' => 'дата направления',
            'status' => 'статус',
            'protocol_number' => 'номер протокола ВК',
            'protocol_date' => 'дата протокола ВК',
            'referral_kind' => 'вид направления',
            'consent_date' => 'дата согласия',
            'consent_document' => 'подписанное разрешение на проведение МСЭ',
            'examination_format' => 'форма проведения МСЭ',
            'notification_method' => 'способ уведомления',
            'purposes' => 'цели направления',
            'observed_since_date' => 'дата начала наблюдения пациента',
            'health_status' => 'психический статус',
            'disease_history' => 'анамнез заболевания',
            'life_history' => 'анамнез жизни',
            'complaints' => 'жалобы или сведения со слов представителя',
            'education_class' => 'класс / группа',
            'medical_rehabilitation' => 'мероприятия медицинской реабилитации',
        ];
    }

    private function minimumObservationPeriodRequired(): bool
    {
        if ($this->input('status') !== MseReferral::STATUS_PREPARED) {
            return false;
        }

        if ($this->input('referral_kind') !== MseReferral::REFERRAL_INITIAL) {
            return false;
        }

        $purposes = collect((array) $this->input('purposes', []))
            ->map(static fn (mixed $value): string => trim((string) $value));

        return $purposes->contains(
            static fn (string $purpose): bool => in_array(
                $purpose,
                self::DISABILITY_ESTABLISHMENT_PURPOSES,
                true
            )
        );
    }

    /**
     * @param array<int|string, mixed> $rows
     * @param array<int, string> $fields
     * @return array<int, array<string, string|null>>
     */
    private function normalizeRows(array $rows, array $fields): array
    {
        return collect($rows)
            ->filter(static fn (mixed $row): bool => is_array($row))
            ->map(static function (array $row) use ($fields): array {
                $normalized = [];

                foreach ($fields as $field) {
                    $value = trim((string) ($row[$field] ?? ''));
                    $normalized[$field] = $value !== '' ? $value : null;
                }

                return $normalized;
            })
            ->filter(static fn (array $row): bool => collect($row)->filter(static fn (mixed $value): bool => $value !== null)->isNotEmpty())
            ->values()
            ->all();
    }
}
