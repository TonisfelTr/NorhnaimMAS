<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMseReferralRequest;
use App\Models\Doctor;
use App\Models\MseReferral;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MseReferralController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $baseQuery = MseReferral::query()
            ->with(['patient.diagnose', 'creator'])
            ->when(
                !$this->isDebug(),
                static fn (Builder $query) => $query->where('clinic_id', $doctor?->clinic_id)
            );

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)
                ->where('status', MseReferral::STATUS_DRAFT)
                ->count(),
            'prepared' => (clone $baseQuery)
                ->where('status', MseReferral::STATUS_PREPARED)
                ->count(),
        ];

        $documents = $baseQuery
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = trim($request->string('q')->toString());
                $like = '%' . $term . '%';

                $query->where(function (Builder $query) use ($like): void {
                    $query
                        ->where('document_number', 'ilike', $like)
                        ->orWhere('protocol_number', 'ilike', $like)
                        ->orWhere('diagnosis_code', 'ilike', $like)
                        ->orWhere('diagnosis_title', 'ilike', $like)
                        ->orWhereHas('patient', function (Builder $patientQuery) use ($like): void {
                            $patientQuery
                                ->where('surname', 'ilike', $like)
                                ->orWhere('name', 'ilike', $like)
                                ->orWhere('patronym', 'ilike', $like);
                        });
                });
            })
            ->when(
                $request->filled('status')
                && array_key_exists($request->string('status')->toString(), MseReferral::statusOptions()),
                static fn (Builder $query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('referral_kind')
                && array_key_exists($request->string('referral_kind')->toString(), MseReferral::referralKindOptions()),
                static fn (Builder $query) => $query->where('referral_kind', $request->string('referral_kind')->toString())
            )
            ->when(
                $request->filled('purpose')
                && array_key_exists($request->string('purpose')->toString(), MseReferral::purposeOptions()),
                static fn (Builder $query) => $query->whereJsonContains('purposes', $request->string('purpose')->toString())
            )
            ->when(
                $this->isIsoDate($request->input('date_from')),
                static fn (Builder $query) => $query->whereDate('document_date', '>=', $request->input('date_from'))
            )
            ->when(
                $this->isIsoDate($request->input('date_to')),
                static fn (Builder $query) => $query->whereDate('document_date', '<=', $request->input('date_to'))
            )
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('doctors.commissions.mse.index', [
            'documents' => $documents,
            'stats' => $stats,
            'statusOptions' => MseReferral::statusOptions(),
            'referralKindOptions' => MseReferral::referralKindOptions(),
            'purposeOptions' => MseReferral::purposeOptions(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $document = new MseReferral([
            'document_date' => today(),
            'status' => MseReferral::STATUS_DRAFT,
            'referral_kind' => MseReferral::REFERRAL_INITIAL,
            'examination_format' => MseReferral::EXAMINATION_WITHOUT_PRESENCE,
            'notification_method' => MseReferral::NOTIFICATION_PHONE_SMS,
            'purposes' => [],
            'patient_snapshot' => [],
            'details' => [],
        ]);

        $patient = null;

        if ($request->integer('patient_id')) {
            /*
             * Неполная карточка пациента не мешает создать черновик.
             * Жёсткая проверка выполняется только при переводе направления
             * в статус «Оформлено».
             */
            $patient = $this->patientQuery($doctor)->find($request->integer('patient_id'));
        }

        $document->setRelation('patient', $patient);

        return $this->formView($document, false, $doctor);
    }

    public function store(SaveMseReferralRequest $request): RedirectResponse
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);
        $data = $this->mergeMseGateConfirmations($request->validated(), $request);
        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);
        $this->ensureMseReferralCanBePrepared($data, $patient, $request);
        $consentFile = $request->file('consent_document');
        $storedConsent = null;

        try {
            $document = DB::transaction(function () use (
                $data,
                $doctor,
                $patient,
                $consentFile,
                &$storedConsent
            ): MseReferral {
                $document = MseReferral::query()->create(
                    $this->documentPayload($data, $patient, $doctor)
                );

                $document->update([
                    'document_number' => sprintf(
                        'МСЭ-%s-%06d',
                        Carbon::parse($document->document_date)->format('Y'),
                        $document->id
                    ),
                ]);

                if ($consentFile instanceof UploadedFile) {
                    $storedConsent = $this->storeConsentDocument($document, $consentFile, $doctor);
                    $document->update($storedConsent);
                }

                return $document;
            });
        } catch (Throwable $exception) {
            $this->deleteConsentDocumentFromStorage($storedConsent);
            throw $exception;
        }

        return redirect()
            ->route('doctors.commissions.mse.show', $document)
            ->with('status', 'success')
            ->with('message', 'Направление на МСЭ создано.');
    }

    public function show(MseReferral $mseReferral): View
    {
        $this->authorizeDocument($mseReferral);
        $mseReferral->load(['patient.diagnose', 'creator', 'consentDocumentUploader']);

        $preparationIssues = $this->msePreparationIssuesForDocument($mseReferral);

        return view('doctors.commissions.mse.show', [
            'document' => $mseReferral,
            'preparationIssues' => $preparationIssues,
            'canBePrepared' => $preparationIssues === [],
            'ageDocumentationProfile' => $this->mseAgeDocumentationProfileForDocument($mseReferral),
            'purposeOptions' => MseReferral::purposeOptions(),
            'sexOptions' => MseReferral::sexOptions(),
            'citizenshipOptions' => MseReferral::citizenshipOptions(),
            'militaryDutyOptions' => MseReferral::militaryDutyOptions(),
            'locationTypeOptions' => MseReferral::locationTypeOptions(),
            'disabilityGroupOptions' => MseReferral::disabilityGroupOptions(),
            'disabilityPeriodOptions' => MseReferral::disabilityPeriodOptions(),
            'disabilityCauseOptions' => MseReferral::disabilityCauseOptions(),
            'prognosisOptions' => MseReferral::prognosisOptions(),
            'rehabilitationPotentialOptions' => MseReferral::rehabilitationPotentialOptions(),
            'rehabilitationEffectOptions' => MseReferral::rehabilitationEffectOptions(),
        ]);
    }

    public function edit(MseReferral $mseReferral): View
    {
        $this->authorizeDocument($mseReferral);
        $mseReferral->load(['patient.diagnose', 'creator']);

        return $this->formView($mseReferral, true, $this->currentDoctor());
    }

    public function update(
        SaveMseReferralRequest $request,
        MseReferral $mseReferral
    ): RedirectResponse {
        $this->authorizeDocument($mseReferral);
        $doctor = $this->currentDoctor();
        $data = $this->mergeMseGateConfirmations($request->validated(), $request);
        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);
        $this->ensureMseReferralCanBePrepared($data, $patient, $request, $mseReferral);
        $consentFile = $request->file('consent_document');
        $removeConsent = $request->boolean('remove_consent_document');
        $previousConsent = $this->consentDocumentStorageReference($mseReferral);
        $storedConsent = null;

        try {
            DB::transaction(function () use (
                $data,
                $doctor,
                $patient,
                $mseReferral,
                $consentFile,
                $removeConsent,
                &$storedConsent
            ): void {
                $payload = $this->documentPayload($data, $patient, $doctor, $mseReferral);

                if ($consentFile instanceof UploadedFile) {
                    $storedConsent = $this->storeConsentDocument($mseReferral, $consentFile, $doctor);
                    $payload = array_merge($payload, $storedConsent);
                } elseif ($removeConsent) {
                    $payload = array_merge($payload, $this->emptyConsentDocumentPayload());
                }

                $mseReferral->update($payload);
            });
        } catch (Throwable $exception) {
            $this->deleteConsentDocumentFromStorage($storedConsent);
            throw $exception;
        }

        if ($consentFile instanceof UploadedFile || $removeConsent) {
            $this->deleteConsentDocumentFromStorage($previousConsent);
        }

        return redirect()
            ->route('doctors.commissions.mse.show', $mseReferral)
            ->with('status', 'success')
            ->with('message', 'Изменения сохранены.');
    }

    public function destroy(MseReferral $mseReferral): RedirectResponse
    {
        $this->authorizeDocument($mseReferral);

        if ($mseReferral->status === MseReferral::STATUS_PREPARED) {
            return redirect()
                ->route('doctors.commissions.mse.index')
                ->with('status', 'error')
                ->with('message', 'Оформленное направление нельзя удалить. Сначала верните его в черновик.');
        }

        $consentDocument = $this->consentDocumentStorageReference($mseReferral);
        $mseReferral->delete();
        $this->deleteConsentDocumentFromStorage($consentDocument);

        return redirect()
            ->route('doctors.commissions.mse.index')
            ->with('status', 'success')
            ->with('message', 'Черновик удалён.');
    }

    public function print(MseReferral $mseReferral): View
    {
        $this->authorizeDocument($mseReferral);
        $mseReferral->load(['patient.diagnose', 'creator']);

        return view('doctors.commissions.mse.print', [
            'document' => $mseReferral,
            'purposeOptions' => MseReferral::purposeOptions(),
            'sexOptions' => MseReferral::sexOptions(),
            'citizenshipOptions' => MseReferral::citizenshipOptions(),
            'militaryDutyOptions' => MseReferral::militaryDutyOptions(),
            'locationTypeOptions' => MseReferral::locationTypeOptions(),
            'disabilityGroupOptions' => MseReferral::disabilityGroupOptions(),
            'disabilityPeriodOptions' => MseReferral::disabilityPeriodOptions(),
            'disabilityCauseOptions' => MseReferral::disabilityCauseOptions(),
            'prognosisOptions' => MseReferral::prognosisOptions(),
            'rehabilitationPotentialOptions' => MseReferral::rehabilitationPotentialOptions(),
            'rehabilitationEffectOptions' => MseReferral::rehabilitationEffectOptions(),
        ]);
    }

    /**
     * Открывает редактор согласия без сохранения направления.
     *
     * mode=auto   — подставляет пациента и текущие значения формы;
     * mode=manual — открывает чистый бланк для ручного заполнения.
     */
    public function consentPreview(Request $request): View
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $mode = strtolower(trim((string) $request->input('mode', 'auto')));
        $mode = in_array($mode, ['auto', 'manual'], true) ? $mode : 'auto';

        if ($mode === 'manual') {
            return view('doctors.commissions.mse.consent-print', [
                'document' => new MseReferral(),
                'consentValues' => $this->emptyConsentEditorValues(),
                'consentMode' => 'manual',
                'isPreview' => true,
            ]);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'document_date' => ['nullable', 'date'],
            'consent_date' => ['nullable', 'date'],
            'examination_format' => [
                'required',
                'string',
                Rule::in(array_keys(MseReferral::examinationFormatOptions())),
            ],
            'notification_method' => [
                'nullable',
                'string',
                Rule::in(array_keys(MseReferral::notificationMethodOptions())),
            ],
            'purposes' => ['nullable', 'array', 'max:11'],
            'purposes.*' => [
                'nullable',
                'string',
                Rule::in(array_keys(MseReferral::purposeOptions())),
            ],

            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_address' => ['nullable', 'string', 'max:1000'],
            'bureau_name' => ['nullable', 'string', 'max:255'],

            'patient_address' => ['nullable', 'string', 'max:1000'],
            'patient_phone' => ['nullable', 'string', 'max:100'],
            'patient_snils' => ['nullable', 'string', 'max:50'],
            'patient_oms' => ['nullable', 'string', 'max:100'],
            'patient_identity_name' => ['nullable', 'string', 'max:255'],
            'patient_identity_series' => ['nullable', 'string', 'max:50'],
            'patient_identity_number' => ['nullable', 'string', 'max:50'],
            'patient_identity_issued_by' => ['nullable', 'string', 'max:1000'],
            'patient_identity_issue_date' => ['nullable', 'date'],

            'representative_full_name' => ['nullable', 'string', 'max:255'],
            'representative_birth_date' => ['nullable', 'date'],
            'representative_authority_document' => ['nullable', 'string', 'max:1000'],
            'representative_identity' => ['nullable', 'string', 'max:1000'],
            'representative_phone' => ['nullable', 'string', 'max:100'],
            'representative_snils' => ['nullable', 'string', 'max:50'],
        ], [
            'patient_id.required' => 'Сначала выберите пациента для автоматического заполнения.',
            'patient_id.exists' => 'Выбранный пациент не найден.',
            'examination_format.required' => 'Укажите форму проведения МСЭ.',
        ]);

        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);
        $defaults = $this->buildPatientDefaults($patient);
        $input = static fn (string $key, mixed $fallback = null): mixed =>
        array_key_exists($key, $data) ? $data[$key] : $fallback;

        $snapshot = array_replace_recursive($defaults, [
            'address' => $input('patient_address', data_get($defaults, 'address')),
            'phone' => $input('patient_phone', data_get($defaults, 'phone')),
            'snils' => $input('patient_snils', data_get($defaults, 'snils')),
            'oms' => $input('patient_oms', data_get($defaults, 'oms')),
            'identity' => [
                'name' => $input('patient_identity_name', data_get($defaults, 'identity.name')),
                'series' => $input('patient_identity_series', data_get($defaults, 'identity.series')),
                'number' => $input('patient_identity_number', data_get($defaults, 'identity.number')),
                'issued_by' => $input('patient_identity_issued_by', data_get($defaults, 'identity.issued_by')),
                'issue_date' => $input('patient_identity_issue_date', data_get($defaults, 'identity.issue_date')),
            ],
            'representative' => [
                'full_name' => $input('representative_full_name'),
                'birth_date' => $input('representative_birth_date'),
                'authority_document' => $input('representative_authority_document'),
                'identity' => $input('representative_identity'),
                'phone' => $input('representative_phone'),
                'snils' => $input('representative_snils'),
            ],
        ]);

        $purposes = collect($data['purposes'] ?? [])
            ->filter(static fn (mixed $value): bool => filled($value))
            ->values()
            ->all();

        $preview = new MseReferral([
            'document_date' => $data['document_date'] ?? today()->format('Y-m-d'),
            'consent_date' => $data['consent_date'] ?? null,
            'examination_format' => $data['examination_format'],
            'notification_method' => $data['notification_method'] ?? null,
            'purposes' => $purposes,
            'patient_snapshot' => $snapshot,
            'details' => [
                'organization' => [
                    'name' => $data['organization_name'] ?? null,
                    'address' => $data['organization_address'] ?? null,
                    'bureau_name' => $data['bureau_name'] ?? null,
                ],
            ],
            'diagnosis_code' => $patient->diagnose?->code,
            'diagnosis_title' => $patient->diagnose?->title ?? $patient->diagnose?->name,
        ]);

        $preview->setRelation('patient', $patient);
        $preview->setRelation('creator', $doctor);

        return view('doctors.commissions.mse.consent-print', [
            'document' => $preview,
            'consentValues' => $this->buildConsentEditorValues($preview),
            'consentMode' => 'auto',
            'isPreview' => true,
        ]);
    }

    public function consentPrint(MseReferral $mseReferral): View
    {
        $this->authorizeDocument($mseReferral);
        $mseReferral->load(['patient.diagnose', 'creator']);

        return view('doctors.commissions.mse.consent-print', [
            'document' => $mseReferral,
            'consentValues' => $this->buildConsentEditorValues($mseReferral),
            'consentMode' => 'auto',
            'isPreview' => false,
        ]);
    }

    public function consentDocument(MseReferral $mseReferral): StreamedResponse
    {
        $this->authorizeDocument($mseReferral);

        if (!$mseReferral->hasConsentDocument()) {
            abort(404, 'Подписанное разрешение на проведение МСЭ не загружено.');
        }

        $disk = $mseReferral->consentDocumentDisk();
        $path = (string) $mseReferral->consent_document_path;
        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            abort(404, 'Файл разрешения не найден в хранилище.');
        }

        return $storage->response(
            $path,
            $mseReferral->consentDocumentOriginalName(),
            [
                'Content-Type' => $mseReferral->consent_document_mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline'
        );
    }


    /**
     * Значения для редактируемой печатной формы согласия.
     */
    private function buildConsentEditorValues(MseReferral $document): array
    {
        $values = $this->emptyConsentEditorValues();
        $snapshot = (array) ($document->patient_snapshot ?: []);
        $details = (array) ($document->details ?: []);

        $patientName = trim((string) data_get($snapshot, 'full_name'));
        if ($patientName === '') {
            $patientName = collect([
                $document->patient?->surname,
                $document->patient?->name,
                $document->patient?->patronym,
            ])->filter()->implode(' ');
        }

        $patientBirth = data_get($snapshot, 'birth_date')
            ?: $document->patient?->getRawOriginal('birth_at')
                ?: $document->patient?->birth_at;
        $patientAddress = trim((string) data_get($snapshot, 'address'));
        $patientResidence = trim((string) data_get($snapshot, 'location.address')) ?: $patientAddress;
        $patientSnils = trim((string) data_get($snapshot, 'snils'));

        $identityName = trim((string) data_get($snapshot, 'identity.name'));
        $identitySeries = trim((string) data_get($snapshot, 'identity.series'));
        $identityNumber = trim((string) data_get($snapshot, 'identity.number'));
        $identityIssuedBy = trim((string) data_get($snapshot, 'identity.issued_by'));
        $identityIssueDate = data_get($snapshot, 'identity.issue_date');
        $patientIdentity = collect([
            $identityName !== '' ? $identityName : null,
            $identitySeries !== '' ? 'серия ' . $identitySeries : null,
            $identityNumber !== '' ? '№ ' . $identityNumber : null,
            $identityIssuedBy !== '' ? 'выдан ' . $identityIssuedBy : null,
            $identityIssueDate ? 'дата выдачи ' . $this->formatConsentEditorDate($identityIssueDate) : null,
        ])->filter()->implode(', ');

        $representativeName = trim((string) data_get($snapshot, 'representative.full_name'));
        $isRepresentative = $representativeName !== '';
        $representativeBirth = data_get($snapshot, 'representative.birth_date');
        $representativeAddress = trim((string) data_get($snapshot, 'representative.address'));
        $representativeIdentity = trim((string) data_get($snapshot, 'representative.identity'));
        $representativeSnils = trim((string) data_get($snapshot, 'representative.snils'));
        $representativeAuthority = trim((string) data_get($snapshot, 'representative.authority_document'));

        $doctor = $document->creator;
        $doctorName = collect([
            $doctor?->surname,
            $doctor?->name,
            $doctor?->patronym,
        ])->filter()->implode(' ');
        $doctorPosition = trim((string) collect([
            data_get($doctor, 'position.title'),
            data_get($doctor, 'position.name'),
            data_get($doctor, 'position_title'),
            data_get($doctor, 'speciality.title'),
            data_get($doctor, 'speciality.name'),
            data_get($doctor, 'speciality_name'),
        ])->filter()->first());

        $purposeText = collect($document->purposeLabels())
            ->map(static fn (string $label): string => preg_replace('/^5\.\d+\.\s*/u', '', $label) ?: $label)
            ->implode('; ');

        return array_replace($values, [
            'document_number' => trim((string) $document->document_number),
            'document_date' => $this->formatConsentEditorDate($document->document_date),
            'applicant_type' => $isRepresentative ? 'representative' : 'citizen',
            'applicant_full_name' => $isRepresentative ? $representativeName : $patientName,
            'applicant_birth_date' => $this->formatConsentEditorDate($isRepresentative ? $representativeBirth : $patientBirth),
            'applicant_registered_address' => $isRepresentative ? $representativeAddress : $patientAddress,
            'applicant_residential_address' => $isRepresentative ? $representativeAddress : $patientResidence,
            'applicant_identity' => $isRepresentative ? $representativeIdentity : $patientIdentity,
            'applicant_snils' => $isRepresentative ? $representativeSnils : $patientSnils,
            'representative_authority_document' => $isRepresentative ? $representativeAuthority : '',
            'patient_full_name' => $patientName,
            'patient_birth_date' => $this->formatConsentEditorDate($patientBirth),
            'bureau_name' => trim((string) data_get($details, 'organization.bureau_name')),
            'examination_format' => trim((string) $document->examination_format),
            'doctor_name' => $doctorName,
            'doctor_position' => $doctorPosition,
            'organization_name' => trim((string) data_get($details, 'organization.name')),
            'purpose_text' => $purposeText,
            'notification_method' => trim((string) $document->notification_method),
            'applicant_signature_name' => $isRepresentative ? $representativeName : $patientName,
            'doctor_signature_name' => $doctorName,
            'consent_date' => $this->formatConsentEditorDate($document->consent_date ?: $document->document_date),
        ]);
    }

    private function emptyConsentEditorValues(): array
    {
        return [
            'document_number' => '',
            'document_date' => '',
            'applicant_type' => '',
            'applicant_full_name' => '',
            'applicant_birth_date' => '',
            'applicant_registered_address' => '',
            'applicant_residential_address' => '',
            'applicant_identity' => '',
            'applicant_snils' => '',
            'representative_authority_document' => '',
            'patient_full_name' => '',
            'patient_birth_date' => '',
            'bureau_name' => '',
            'examination_format' => '',
            'doctor_name' => '',
            'doctor_position' => '',
            'organization_name' => '',
            'purpose_text' => '',
            'notification_method' => '',
            'applicant_signature_name' => '',
            'doctor_signature_name' => '',
            'consent_date' => '',
        ];
    }

    private function formatConsentEditorDate(mixed $value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (Throwable) {
            return trim((string) $value);
        }
    }

    private function formView(
        MseReferral $document,
        bool $isEditing,
        ?Doctor $doctor
    ): View {
        $document->loadMissing('patient.diagnose');

        $selectedPatientId = old('patient_id', $document->patient_id);
        $patient = $document->patient;

        if ($selectedPatientId && (int) $selectedPatientId !== (int) $patient?->id) {
            $patient = $this->patientQuery($doctor)->find((int) $selectedPatientId);
        }

        $initialPatient = $this->buildPatientPayload($patient);
        $samePatient = $isEditing && (int) $document->patient_id === (int) $patient?->id;
        $patientSnapshot = $samePatient
            ? (array) ($document->patient_snapshot ?: [])
            : $this->buildPatientDefaults($patient);

        if ($initialPatient && $samePatient && $document->status === MseReferral::STATUS_PREPARED) {
            /*
             * После оформления диагноз является снимком документа.
             * Пока направление — черновик, в форме показываем актуальный диагноз
             * из карточки пациента, чтобы отсутствующий диагноз можно было исправить
             * без смены пациента в направлении.
             */
            $diagnosisCode = trim((string) $document->diagnosis_code);
            $diagnosisTitle = trim((string) $document->diagnosis_title);

            $initialPatient['diagnosis'] = [
                'id' => null,
                'code' => $diagnosisCode !== '' ? $diagnosisCode : null,
                'title' => $diagnosisTitle !== '' ? $diagnosisTitle : null,
            ];
            $initialPatient['diagnosis_label'] = ($diagnosisCode !== '' || $diagnosisTitle !== '')
                ? $diagnosisCode
                . ($diagnosisCode !== '' && $diagnosisTitle !== '' ? ' — ' : '')
                . $diagnosisTitle
                : 'не установлен';
        }

        return view('doctors.commissions.mse.form', [
            'document' => $document,
            'isEditing' => $isEditing,
            'pageTitle' => $isEditing ? 'Редактирование направления на МСЭ' : 'Новое направление на МСЭ',
            'headingTitle' => $isEditing
                ? ($document->document_number ?: 'Редактирование направления')
                : 'Новое направление на МСЭ',
            'initialPatient' => $initialPatient,
            'selectedPatientId' => $selectedPatientId,
            'formValues' => $this->buildFormValues($document, $patientSnapshot, $doctor),
            'purposes' => old('purposes', $document->purposes ?: []),
            'temporaryDisabilityRows' => old(
                'temporary_disability',
                data_get($document->details, 'clinical.temporary_disability', []) ?: []
            ),
            'examinationRows' => old(
                'examinations',
                data_get($document->details, 'clinical.examinations', []) ?: []
            ),
            'commissionMembers' => old(
                'commission_members',
                data_get($document->details, 'commission.members', []) ?: []
            ),
            'statusOptions' => MseReferral::statusOptions(),
            'referralKindOptions' => MseReferral::referralKindOptions(),
            'examinationFormatOptions' => MseReferral::examinationFormatOptions(),
            'notificationMethodOptions' => MseReferral::notificationMethodOptions(),
            'purposeOptions' => MseReferral::purposeOptions(),
            'sexOptions' => MseReferral::sexOptions(),
            'citizenshipOptions' => MseReferral::citizenshipOptions(),
            'militaryDutyOptions' => MseReferral::militaryDutyOptions(),
            'locationTypeOptions' => MseReferral::locationTypeOptions(),
            'disabilityGroupOptions' => MseReferral::disabilityGroupOptions(),
            'disabilityPeriodOptions' => MseReferral::disabilityPeriodOptions(),
            'disabilityCauseOptions' => MseReferral::disabilityCauseOptions(),
            'prognosisOptions' => MseReferral::prognosisOptions(),
            'rehabilitationPotentialOptions' => MseReferral::rehabilitationPotentialOptions(),
            'rehabilitationEffectOptions' => MseReferral::rehabilitationEffectOptions(),
        ]);
    }

    private function documentPayload(
        array $data,
        Patient $patient,
        ?Doctor $doctor,
        ?MseReferral $document = null
    ): array {
        $preparedAt = $document?->prepared_at;

        if ($data['status'] === MseReferral::STATUS_PREPARED && !$preparedAt) {
            $preparedAt = now();
        }

        if ($data['status'] !== MseReferral::STATUS_PREPARED) {
            $preparedAt = null;
        }

        $patientChanged = !$document || (int) $document->patient_id !== (int) $patient->id;
        $snapshotBase = $patientChanged
            ? $this->buildPatientDefaults($patient)
            : (array) ($document?->patient_snapshot ?: []);

        $payload = [
            'patient_id' => $patient->id,
            'document_date' => $data['document_date'],
            'status' => $data['status'],
            'protocol_number' => $data['protocol_number'] ?? null,
            'protocol_date' => $data['protocol_date'] ?? null,
            'referral_kind' => $data['referral_kind'],
            'consent_date' => $data['consent_date'] ?? null,
            'examination_format' => $data['examination_format'],
            'notification_method' => $data['notification_method'] ?? null,
            'requires_home_examination' => (bool) ($data['requires_home_examination'] ?? false),
            'palliative_care' => (bool) ($data['palliative_care'] ?? false),
            'primary_prosthetics' => (bool) ($data['primary_prosthetics'] ?? false),
            'purposes' => array_values($data['purposes'] ?? []),
            'patient_snapshot' => $this->buildPatientSnapshot($snapshotBase, $patient, $data),
            'details' => $this->buildDetails($data),
            'prepared_at' => $preparedAt,
        ];

        $refreshDiagnosis = $patientChanged
            || !$document
            || $document->status !== MseReferral::STATUS_PREPARED
            || $data['status'] !== MseReferral::STATUS_PREPARED;

        if ($refreshDiagnosis) {
            $payload['diagnosis_code'] = $patient->diagnose?->code;
            $payload['diagnosis_title'] = $patient->diagnose?->title ?? $patient->diagnose?->name;
        }

        if (!$document) {
            $payload['created_by_doctor_id'] = $doctor?->id;
            $payload['clinic_id'] = $doctor?->clinic_id;
        }

        return $payload;
    }

    private function buildPatientSnapshot(array $base, Patient $patient, array $data): array
    {
        $snapshot = array_replace_recursive($this->buildPatientDefaults($patient), $base);

        $snapshot['full_name'] = collect([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])->filter()->implode(' ');
        $snapshot['birth_date'] = $this->patientBirthDate($patient);
        $snapshot['age'] = $this->patientAge($patient);
        $snapshot['age_months'] = $this->patientAgeMonths($patient);
        $snapshot['disability'] = (bool) data_get($patient, 'disability', false);

        $snapshot['sex'] = $data['patient_sex'] ?? null;
        $snapshot['citizenship'] = [
            'type' => $data['patient_citizenship'] ?? null,
            'country' => $data['patient_citizenship_country'] ?? null,
        ];
        $snapshot['military_duty'] = $data['patient_military_duty'] ?? null;
        $snapshot['address'] = $data['patient_address'] ?? null;
        $snapshot['homeless'] = (bool) ($data['patient_homeless'] ?? false);
        $snapshot['location'] = [
            'type' => $data['patient_location_type'] ?? null,
            'name' => $data['patient_location_name'] ?? null,
            'address' => $data['patient_location_address'] ?? null,
            'ogrn' => $data['patient_location_ogrn'] ?? null,
        ];
        $snapshot['phone'] = $data['patient_phone'] ?? null;
        $snapshot['email'] = $data['patient_email'] ?? null;
        $snapshot['snils'] = $data['patient_snils'] ?? null;
        $snapshot['oms'] = $data['patient_oms'] ?? null;
        $snapshot['identity'] = [
            'name' => $data['patient_identity_name'] ?? null,
            'series' => $data['patient_identity_series'] ?? null,
            'number' => $data['patient_identity_number'] ?? null,
            'issued_by' => $data['patient_identity_issued_by'] ?? null,
            'issue_date' => $data['patient_identity_issue_date'] ?? null,
        ];
        $snapshot['representative'] = [
            'full_name' => $data['representative_full_name'] ?? null,
            'birth_date' => $data['representative_birth_date'] ?? null,
            'authority_document' => $data['representative_authority_document'] ?? null,
            'identity' => $data['representative_identity'] ?? null,
            'phone' => $data['representative_phone'] ?? null,
            'email' => $data['representative_email'] ?? null,
            'snils' => $data['representative_snils'] ?? null,
        ];
        $snapshot['guardian_organization'] = [
            'name' => $data['guardian_organization_name'] ?? null,
            'address' => $data['guardian_organization_address'] ?? null,
            'ogrn' => $data['guardian_organization_ogrn'] ?? null,
        ];

        return $snapshot;
    }

    private function buildDetails(array $data): array
    {
        $height = isset($data['height_cm']) ? (float) $data['height_cm'] : null;
        $weight = isset($data['weight_kg']) ? (float) $data['weight_kg'] : null;
        $bmi = isset($data['bmi']) ? (float) $data['bmi'] : null;

        if (!$bmi && $height && $weight && $height > 0) {
            $bmi = round($weight / (($height / 100) ** 2), 1);
        }

        return [
            'organization' => [
                'name' => $data['organization_name'] ?? null,
                'address' => $data['organization_address'] ?? null,
                'ogrn' => $data['organization_ogrn'] ?? null,
                'bureau_name' => $data['bureau_name'] ?? null,
            ],
            'previous_mse' => [
                'disability_group' => $data['previous_disability_group'] ?? null,
                'disability_until' => $data['previous_disability_until'] ?? null,
                'disability_period' => $data['previous_disability_period'] ?? null,
                'disability_cause' => $data['previous_disability_cause'] ?? null,
                'disability_cause_other' => $data['previous_disability_cause_other'] ?? null,
                'professional_loss_percent' => $data['previous_professional_loss_percent'] ?? null,
                'professional_loss_term' => $data['previous_professional_loss_term'] ?? null,
                'professional_loss_until' => $data['previous_professional_loss_until'] ?? null,
                'professional_loss_other' => $data['previous_professional_loss_other'] ?? null,
            ],
            'education' => [
                'organization' => $data['education_organization'] ?? null,
                'class' => $data['education_class'] ?? null,
                'level' => $data['education_level'] ?? null,
                'profession' => $data['education_profession'] ?? null,
            ],
            'employment' => [
                'profession' => $data['employment_profession'] ?? null,
                'qualification' => $data['employment_qualification'] ?? null,
                'experience' => $data['employment_experience'] ?? null,
                'current_work' => $data['employment_current_work'] ?? null,
                'conditions' => $data['employment_conditions'] ?? null,
                'organization' => $data['employment_organization'] ?? null,
                'address' => $data['employment_address'] ?? null,
            ],
            'clinical' => [
                'observed_since_date' => $data['observed_since_date'] ?? null,
                'observed_since_year' => $data['observed_since_year'] ?? null,
                'disease_history' => $data['disease_history'] ?? null,
                'life_history' => $data['life_history'] ?? null,
                'temporary_disability' => array_values($data['temporary_disability'] ?? []),
                'has_eln' => (bool) ($data['has_eln'] ?? false),
                'eln_number' => $data['eln_number'] ?? null,
                'ipra' => [
                    'number' => $data['ipra_number'] ?? null,
                    'protocol_number' => $data['ipra_protocol_number'] ?? null,
                    'protocol_date' => $data['ipra_protocol_date'] ?? null,
                ],
                'rehabilitation_results' => [
                    'function_restoration' => $data['rehab_function_restoration'] ?? null,
                    'function_compensation' => $data['rehab_function_compensation'] ?? null,
                    'notes' => $data['rehab_results_notes'] ?? null,
                ],
                'anthropometry' => [
                    'height_cm' => $height,
                    'weight_kg' => $weight,
                    'bmi' => $bmi,
                    'body_build' => $data['body_build'] ?? null,
                    'daily_excretions_ml' => $data['daily_excretions_ml'] ?? null,
                    'waist_cm' => $data['waist_cm'] ?? null,
                    'hip_cm' => $data['hip_cm'] ?? null,
                    'birth_weight_g' => $data['birth_weight_g'] ?? null,
                    'physical_development' => $data['physical_development'] ?? null,
                ],
                'health_status' => $data['health_status'] ?? null,
                'complaints' => $data['complaints'] ?? null,
                'examinations' => array_values($data['examinations'] ?? []),
                'complications' => $data['complications'] ?? null,
                'concomitant_diseases' => $data['concomitant_diseases'] ?? null,
                'concomitant_codes' => $data['concomitant_codes'] ?? null,
            ],
            'rehabilitation' => [
                'clinical_prognosis' => $data['clinical_prognosis'] ?? null,
                'potential' => $data['rehabilitation_potential'] ?? null,
                'prognosis' => $data['rehabilitation_prognosis'] ?? null,
                'medical_rehabilitation' => $data['medical_rehabilitation'] ?? null,
                'medications_devices' => $data['medications_devices'] ?? null,
                'reconstructive_surgery' => $data['reconstructive_surgery'] ?? null,
                'prosthetics_orthotics' => $data['prosthetics_orthotics'] ?? null,
                'sanatorium_treatment' => $data['sanatorium_treatment'] ?? null,
                'special_medical_care' => $data['special_medical_care'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
            ],
            'commission' => [
                'chairman' => $data['commission_chairman'] ?? null,
                'members' => array_values($data['commission_members'] ?? []),
            ],
            'mse_gate' => [
                'persistent' => (bool) ($data['mse_gate_persistent'] ?? false),
                'measures' => (bool) ($data['mse_gate_measures'] ?? false),
                'exams' => (bool) ($data['mse_gate_exams'] ?? false),
            ],
        ];
    }

    private function buildFormValues(MseReferral $document, array $patientSnapshot, ?Doctor $doctor): array
    {
        $details = (array) ($document->details ?: []);
        $chairmanDefault = collect([
            $doctor?->surname,
            $doctor?->name,
            $doctor?->patronym,
        ])->filter()->implode(' ');

        return [
            'document_date' => $document->document_date?->format('Y-m-d') ?: today()->format('Y-m-d'),
            'status' => $document->status ?: MseReferral::STATUS_DRAFT,
            'protocol_number' => $document->protocol_number,
            'protocol_date' => $document->protocol_date?->format('Y-m-d'),
            'referral_kind' => $document->referral_kind ?: MseReferral::REFERRAL_INITIAL,
            'consent_date' => $document->consent_date?->format('Y-m-d'),
            'examination_format' => $document->examination_format ?: MseReferral::EXAMINATION_WITHOUT_PRESENCE,
            'notification_method' => $document->notification_method ?: MseReferral::NOTIFICATION_PHONE_SMS,
            'requires_home_examination' => (bool) $document->requires_home_examination,
            'palliative_care' => (bool) $document->palliative_care,
            'primary_prosthetics' => (bool) $document->primary_prosthetics,

            'organization_name' => data_get($details, 'organization.name', config('app.name')),
            'organization_address' => data_get($details, 'organization.address'),
            'organization_ogrn' => data_get($details, 'organization.ogrn'),
            'bureau_name' => data_get($details, 'organization.bureau_name'),

            'patient_sex' => data_get($patientSnapshot, 'sex'),
            'patient_citizenship' => data_get($patientSnapshot, 'citizenship.type'),
            'patient_citizenship_country' => data_get($patientSnapshot, 'citizenship.country'),
            'patient_military_duty' => data_get($patientSnapshot, 'military_duty'),
            'patient_address' => data_get($patientSnapshot, 'address'),
            'patient_homeless' => (bool) data_get($patientSnapshot, 'homeless', false),
            'patient_location_type' => data_get($patientSnapshot, 'location.type', 'residence'),
            'patient_location_name' => data_get($patientSnapshot, 'location.name'),
            'patient_location_address' => data_get($patientSnapshot, 'location.address'),
            'patient_location_ogrn' => data_get($patientSnapshot, 'location.ogrn'),
            'patient_phone' => data_get($patientSnapshot, 'phone'),
            'patient_email' => data_get($patientSnapshot, 'email'),
            'patient_snils' => data_get($patientSnapshot, 'snils'),
            'patient_oms' => data_get($patientSnapshot, 'oms'),
            'patient_identity_name' => data_get($patientSnapshot, 'identity.name'),
            'patient_identity_series' => data_get($patientSnapshot, 'identity.series'),
            'patient_identity_number' => data_get($patientSnapshot, 'identity.number'),
            'patient_identity_issued_by' => data_get($patientSnapshot, 'identity.issued_by'),
            'patient_identity_issue_date' => $this->formatDateInput(data_get($patientSnapshot, 'identity.issue_date')),
            'representative_full_name' => data_get($patientSnapshot, 'representative.full_name'),
            'representative_birth_date' => $this->formatDateInput(data_get($patientSnapshot, 'representative.birth_date')),
            'representative_authority_document' => data_get($patientSnapshot, 'representative.authority_document'),
            'representative_identity' => data_get($patientSnapshot, 'representative.identity'),
            'representative_phone' => data_get($patientSnapshot, 'representative.phone'),
            'representative_email' => data_get($patientSnapshot, 'representative.email'),
            'representative_snils' => data_get($patientSnapshot, 'representative.snils'),
            'guardian_organization_name' => data_get($patientSnapshot, 'guardian_organization.name'),
            'guardian_organization_address' => data_get($patientSnapshot, 'guardian_organization.address'),
            'guardian_organization_ogrn' => data_get($patientSnapshot, 'guardian_organization.ogrn'),

            'previous_disability_group' => data_get($details, 'previous_mse.disability_group'),
            'previous_disability_until' => $this->formatDateInput(data_get($details, 'previous_mse.disability_until')),
            'previous_disability_period' => data_get($details, 'previous_mse.disability_period'),
            'previous_disability_cause' => data_get($details, 'previous_mse.disability_cause'),
            'previous_disability_cause_other' => data_get($details, 'previous_mse.disability_cause_other'),
            'previous_professional_loss_percent' => data_get($details, 'previous_mse.professional_loss_percent'),
            'previous_professional_loss_term' => data_get($details, 'previous_mse.professional_loss_term'),
            'previous_professional_loss_until' => $this->formatDateInput(data_get($details, 'previous_mse.professional_loss_until')),
            'previous_professional_loss_other' => data_get($details, 'previous_mse.professional_loss_other'),

            'education_organization' => data_get($details, 'education.organization'),
            'education_class' => data_get($details, 'education.class'),
            'education_level' => data_get($details, 'education.level'),
            'education_profession' => data_get($details, 'education.profession'),
            'employment_profession' => data_get($details, 'employment.profession'),
            'employment_qualification' => data_get($details, 'employment.qualification'),
            'employment_experience' => data_get($details, 'employment.experience'),
            'employment_current_work' => data_get($details, 'employment.current_work'),
            'employment_conditions' => data_get($details, 'employment.conditions'),
            'employment_organization' => data_get($details, 'employment.organization'),
            'employment_address' => data_get($details, 'employment.address'),

            'observed_since_date' => $this->formatDateInput(
                data_get($details, 'clinical.observed_since_date')
            ),
            'observed_since_year' => data_get($details, 'clinical.observed_since_year'),
            'disease_history' => data_get($details, 'clinical.disease_history'),
            'life_history' => data_get($details, 'clinical.life_history'),
            'has_eln' => (bool) data_get($details, 'clinical.has_eln', false),
            'eln_number' => data_get($details, 'clinical.eln_number'),
            'ipra_number' => data_get($details, 'clinical.ipra.number'),
            'ipra_protocol_number' => data_get($details, 'clinical.ipra.protocol_number'),
            'ipra_protocol_date' => $this->formatDateInput(data_get($details, 'clinical.ipra.protocol_date')),
            'rehab_function_restoration' => data_get($details, 'clinical.rehabilitation_results.function_restoration'),
            'rehab_function_compensation' => data_get($details, 'clinical.rehabilitation_results.function_compensation'),
            'rehab_results_notes' => data_get($details, 'clinical.rehabilitation_results.notes'),
            'height_cm' => data_get($details, 'clinical.anthropometry.height_cm'),
            'weight_kg' => data_get($details, 'clinical.anthropometry.weight_kg'),
            'bmi' => data_get($details, 'clinical.anthropometry.bmi'),
            'body_build' => data_get($details, 'clinical.anthropometry.body_build'),
            'daily_excretions_ml' => data_get($details, 'clinical.anthropometry.daily_excretions_ml'),
            'waist_cm' => data_get($details, 'clinical.anthropometry.waist_cm'),
            'hip_cm' => data_get($details, 'clinical.anthropometry.hip_cm'),
            'birth_weight_g' => data_get($details, 'clinical.anthropometry.birth_weight_g'),
            'physical_development' => data_get($details, 'clinical.anthropometry.physical_development'),
            'health_status' => data_get($details, 'clinical.health_status'),
            'complaints' => data_get($details, 'clinical.complaints'),
            'complications' => data_get($details, 'clinical.complications'),
            'concomitant_diseases' => data_get($details, 'clinical.concomitant_diseases'),
            'concomitant_codes' => data_get($details, 'clinical.concomitant_codes'),

            'clinical_prognosis' => data_get($details, 'rehabilitation.clinical_prognosis'),
            'rehabilitation_potential' => data_get($details, 'rehabilitation.potential'),
            'rehabilitation_prognosis' => data_get($details, 'rehabilitation.prognosis'),
            'medical_rehabilitation' => data_get($details, 'rehabilitation.medical_rehabilitation'),
            'medications_devices' => data_get($details, 'rehabilitation.medications_devices'),
            'reconstructive_surgery' => data_get($details, 'rehabilitation.reconstructive_surgery'),
            'prosthetics_orthotics' => data_get($details, 'rehabilitation.prosthetics_orthotics'),
            'sanatorium_treatment' => data_get($details, 'rehabilitation.sanatorium_treatment'),
            'special_medical_care' => data_get($details, 'rehabilitation.special_medical_care'),
            'additional_notes' => data_get($details, 'rehabilitation.additional_notes'),
            'commission_chairman' => data_get($details, 'commission.chairman', $chairmanDefault),
            'mse_gate_persistent' => (bool) data_get($details, 'mse_gate.persistent', false),
            'mse_gate_measures' => (bool) data_get($details, 'mse_gate.measures', false),
            'mse_gate_exams' => (bool) data_get($details, 'mse_gate.exams', false),
        ];
    }

    private function buildPatientDefaults(?Patient $patient): array
    {
        if (!$patient) {
            return [];
        }

        $rawSex = mb_strtolower(trim((string) (data_get($patient, 'sex') ?? data_get($patient, 'gender') ?? '')));
        $sex = null;

        if (in_array($rawSex, ['male', 'm', 'м', 'мужской', 'муж'], true)) {
            $sex = 'male';
        } elseif (in_array($rawSex, ['female', 'f', 'ж', 'женский', 'жен'], true)) {
            $sex = 'female';
        }

        $address = data_get($patient, 'address_registration')
            ?? data_get($patient, 'registration_address')
            ?? data_get($patient, 'address')
            ?? data_get($patient, 'address_residence');

        $phone = data_get($patient, 'phone')
            ?? data_get($patient, 'telephone')
            ?? data_get($patient, 'mobile_phone');

        $oms = data_get($patient, 'oms')
            ?? data_get($patient, 'insurance_policy')
            ?? data_get($patient, 'policy')
            ?? data_get($patient, 'oms_policy');

        $age = $this->patientAge($patient);
        $identityDefaultName = $age !== null && $age < 14
            ? 'Свидетельство о рождении'
            : 'Паспорт гражданина Российской Федерации';

        return [
            'full_name' => collect([
                $patient->surname,
                $patient->name,
                $patient->patronym,
            ])->filter()->implode(' '),
            'birth_date' => $this->patientBirthDate($patient),
            'age' => $age,
            'age_months' => $this->patientAgeMonths($patient),
            'disability' => (bool) data_get($patient, 'disability', false),
            'sex' => $sex,
            'citizenship' => [
                'type' => null,
                'country' => null,
            ],
            'military_duty' => null,
            'address' => $address,
            'homeless' => false,
            'location' => [
                'type' => 'residence',
                'name' => null,
                'address' => $address,
                'ogrn' => null,
            ],
            'phone' => $phone,
            'email' => data_get($patient, 'email'),
            'snils' => data_get($patient, 'snils') ?? data_get($patient, 'SNILS'),
            'oms' => $oms,
            'identity' => [
                'name' => data_get($patient, 'identity_document_name') ?? $identityDefaultName,
                'series' => data_get($patient, 'passport_series')
                    ?? data_get($patient, 'serial'),
                'number' => data_get($patient, 'passport_number')
                    ?? data_get($patient, 'number'),
                'issued_by' => data_get($patient, 'passport_issued_by')
                    ?? data_get($patient, 'issued_by'),
                'issue_date' => data_get($patient, 'passport_issued_at')
                    ?? data_get($patient, 'passport_issue_date')
                        ?? data_get($patient, 'issued_at'),
            ],
            'representative' => [],
            'guardian_organization' => [],
        ];
    }

    private function buildPatientPayload(?Patient $patient): ?array
    {
        if (!$patient) {
            return null;
        }

        $birthDate = $this->patientBirthDate($patient);
        $birthAtDisplay = $this->formatDateDisplay($birthDate);
        $fullName = collect([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])->filter()->implode(' ');

        $diagnosisCode = trim((string) ($patient->diagnose?->code ?? ''));
        $diagnosisTitle = trim((string) (
            $patient->diagnose?->title
            ?? $patient->diagnose?->name
            ?? ''
        ));

        return [
            'id' => (int) $patient->id,
            'text' => $fullName,
            'full_name' => $fullName,
            'birth_at' => $birthDate,
            'birth_at_display' => $birthAtDisplay,
            'age' => $this->patientAge($patient),
            'age_months' => $this->patientAgeMonths($patient),
            'disability' => (bool) data_get($patient, 'disability', false),
            'diagnosis' => [
                'id' => $patient->diagnose_id,
                'code' => $diagnosisCode !== '' ? $diagnosisCode : null,
                'title' => $diagnosisTitle !== '' ? $diagnosisTitle : null,
            ],
            'diagnosis_label' => ($diagnosisCode !== '' || $diagnosisTitle !== '')
                ? $diagnosisCode
                . ($diagnosisCode !== '' && $diagnosisTitle !== '' ? ' — ' : '')
                . $diagnosisTitle
                : 'не установлен',
            'phone' => data_get($patient, 'phone')
                ?? data_get($patient, 'telephone')
                    ?? data_get($patient, 'mobile_phone'),
            'email' => data_get($patient, 'email'),
            'snils' => data_get($patient, 'snils') ?? data_get($patient, 'SNILS'),
            'oms' => data_get($patient, 'oms')
                ?? data_get($patient, 'insurance_policy')
                    ?? data_get($patient, 'policy')
                    ?? data_get($patient, 'oms_policy'),
            'address' => data_get($patient, 'address_registration')
                ?? data_get($patient, 'registration_address')
                    ?? data_get($patient, 'address')
                    ?? data_get($patient, 'address_residence'),
        ];
    }

    /**
     * Сведения из карточки пациента, без которых новое направление на МСЭ
     * оформлять нельзя.
     *
     * Для пациентов с 14 лет проверяем паспортные реквизиты карточки.
     * Для детей младше 14 лет реквизиты свидетельства о рождении проверяются
     * уже по значениям формы 088/у: в модели Patient они могут храниться
     * не в паспортных колонках.
     *
     * @return array<int, string>
     */
    private function msePatientMissingProfileFields(Patient $patient): array
    {
        $value = static function (mixed ...$candidates): mixed {
            foreach ($candidates as $candidate) {
                if ($candidate === null) {
                    continue;
                }

                if (is_string($candidate) && trim($candidate) === '') {
                    continue;
                }

                return $candidate;
            }

            return null;
        };

        /*
         * Здесь проверяем только базовые сведения, которые должны быть
         * однозначно привязаны к самой карточке пациента.
         *
         * Адрес, контакт, СНИЛС/ОМС и удостоверяющий документ повторно
         * проверяются по снимку/значениям формы 088/у. Это важно, потому что
         * форма разрешает уточнить их перед оформлением, а тип документа
         * зависит от возраста и гражданства.
         */
        $required = [
            'Фамилия' => $value(data_get($patient, 'surname')),
            'Имя' => $value(data_get($patient, 'name')),
            'Дата рождения' => $value($this->patientBirthDate($patient)),
            'Пол' => $value(
                data_get($patient, 'gender'),
                data_get($patient, 'sex')
            ),
        ];

        return collect($required)
            ->filter(static fn (mixed $fieldValue): bool => $fieldValue === null)
            ->keys()
            ->values()
            ->all();
    }

    private function ensureMsePatientProfileComplete(Patient $patient): void
    {
        $missingProfileFields = $this->msePatientMissingProfileFields($patient);

        if ($missingProfileFields === []) {
            return;
        }

        throw ValidationException::withMessages([
            'patient_id' => 'Нельзя оформить направление на МСЭ: в карточке пациента не заполнены обязательные сведения: '
                . implode(', ', $missingProfileFields)
                . '. Сначала заполните данные пациента в медицинской карте.',
        ]);
    }

    /**
     * Чекбоксы внутреннего контроля не входят в обязательную схему FormRequest:
     * черновик можно сохранять без них. Нормализуем значения здесь и сохраняем
     * вместе с details, чтобы состояние было видно после повторного открытия.
     */
    private function mergeMseGateConfirmations(array $data, Request $request): array
    {
        foreach (['mse_gate_persistent', 'mse_gate_measures', 'mse_gate_exams'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        return $data;
    }

    /**
     * Запрещает только перевод в статус «Оформлено».
     * Черновик разрешено сохранять с незаполненными сведениями — все проблемы
     * будут показаны на странице просмотра как список доработки.
     */
    private function ensureMseReferralCanBePrepared(
        array $data,
        Patient $patient,
        Request $request,
        ?MseReferral $document = null
    ): void {
        if (($data['status'] ?? null) !== MseReferral::STATUS_PREPARED) {
            return;
        }

        $issues = $this->msePreparationIssuesFromInput($data, $patient, $request, $document);

        if ($issues === []) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => 'Направление нельзя перевести в статус «Оформлено» и направить в МСЭ. '
                . collect($issues)->pluck('message')->implode(' '),
        ]);
    }

    /**
     * Серверный эквивалент проверки из формы.
     * Нельзя полагаться только на JavaScript: POST можно отправить напрямую.
     *
     * @return array<int, array{key: string, message: string}>
     */
    private function msePreparationIssuesFromInput(
        array $data,
        Patient $patient,
        Request $request,
        ?MseReferral $document = null
    ): array {
        $issues = [];
        $push = static function (array &$target, string $key, string $message): void {
            $target[] = ['key' => $key, 'message' => $message];
        };

        $missingProfileFields = $this->msePatientMissingProfileFields($patient);
        if ($missingProfileFields !== []) {
            $push(
                $issues,
                'patient_profile',
                'В карточке пациента не заполнены обязательные сведения: '
                . implode(', ', $missingProfileFields) . '.'
            );
        }

        $diagnosisCode = trim((string) ($patient->diagnose?->code ?? ''));
        $diagnosisTitle = trim((string) ($patient->diagnose?->title ?? $patient->diagnose?->name ?? ''));

        if ($diagnosisCode === '' && $diagnosisTitle === '') {
            $push($issues, 'diagnosis', 'В карте пациента не установлен основной диагноз.');
        } elseif ($diagnosisCode === '') {
            $push($issues, 'diagnosis_code', 'У основного диагноза отсутствует код МКБ.');
        }

        $age = $this->patientAge($patient);
        if ($age === null) {
            $push($issues, 'age', 'Не удалось определить возраст пациента. Проверьте дату рождения в медицинской карте.');
        }

        $citizenship = trim((string) ($data['patient_citizenship'] ?? ''));
        if ($citizenship === '') {
            $push($issues, 'citizenship', 'Не указано гражданство пациента.');
        }

        if (blank($data['patient_sex'] ?? null)) {
            $push($issues, 'patient_sex', 'Не указан пол пациента.');
        }
        if (blank($data['patient_snils'] ?? null)) {
            $push($issues, 'patient_snils', 'Не указан СНИЛС пациента.');
        }
        if (!(bool) ($data['patient_homeless'] ?? false) && blank($data['patient_address'] ?? null)) {
            $push($issues, 'patient_address', 'Не указан адрес места жительства/пребывания пациента.');
        }

        /*
         * П. 16 формы 088/у заполняется для любого возраста. Для граждан РФ
         * до 14 лет ориентир — свидетельство о рождении, с 14 лет — паспорт.
         * Для иностранного гражданина/лица без гражданства допускается иной
         * удостоверяющий документ, поэтому по тексту наименования не валидируем.
         */
        $identityMissing = [];
        foreach ([
                     'patient_identity_name' => 'наименование документа',
                     'patient_identity_number' => 'номер документа',
                     'patient_identity_issued_by' => 'кем выдан документ',
                     'patient_identity_issue_date' => 'дата выдачи документа',
                 ] as $field => $label) {
            if (blank($data[$field] ?? null)) {
                $identityMissing[] = $label;
            }
        }
        if ($citizenship === 'russian' && blank($data['patient_identity_series'] ?? null)) {
            $identityMissing[] = 'серия документа';
        }
        if ($identityMissing !== []) {
            $identityKind = $citizenship === 'russian' && $age !== null && $age < 14
                ? 'свидетельства о рождении'
                : ($citizenship === 'russian' && $age !== null && $age >= 14
                    ? 'паспорта'
                    : 'документа, удостоверяющего личность');
            $push(
                $issues,
                'identity',
                'Не полностью заполнены реквизиты ' . $identityKind . ': ' . implode(', ', $identityMissing) . '.'
            );
        }

        /*
         * В психиатрическом сценарии до 15 лет права пациента реализует
         * родитель/иной законный представитель. Поэтому перед оформлением
         * требуем заполненный блок п. 17 формы 088/у.
         *
         * С 15 лет представитель не обязателен только по возрасту, но если
         * он указан, его блок должен быть заполнен последовательно.
         */
        $representativeFields = [
            'representative_full_name' => 'ФИО представителя',
            'representative_birth_date' => 'дата рождения представителя',
            'representative_authority_document' => 'документ о полномочиях представителя',
            'representative_identity' => 'документ, удостоверяющий личность представителя',
            'representative_phone' => 'телефон представителя',
            'representative_snils' => 'СНИЛС представителя',
        ];
        $hasAnyRepresentativeData = collect(array_keys($representativeFields))
            ->contains(static fn (string $field): bool => filled($data[$field] ?? null));

        if ($age !== null && $age < 15) {
            $representativeMissing = collect($representativeFields)
                ->filter(static fn (string $label, string $field): bool => blank($data[$field] ?? null))
                ->values()
                ->all();
            if ($representativeMissing !== []) {
                $push(
                    $issues,
                    'representative',
                    'Для пациента младше 15 лет в психиатрическом сценарии необходимо заполнить сведения законного представителя: '
                    . implode(', ', $representativeMissing) . '.'
                );
            }
        } elseif ($hasAnyRepresentativeData) {
            $representativeMissing = collect($representativeFields)
                ->filter(static fn (string $label, string $field): bool => blank($data[$field] ?? null))
                ->values()
                ->all();
            if ($representativeMissing !== []) {
                $push(
                    $issues,
                    'representative',
                    'Блок представителя заполнен не полностью: ' . implode(', ', $representativeMissing) . '.'
                );
            }
        }

        $patientContactPhone = trim((string) ($data['patient_phone'] ?? ''));
        $representativeContactPhone = trim((string) ($data['representative_phone'] ?? ''));
        if ($patientContactPhone === '' && $representativeContactPhone === '') {
            $push($issues, 'contact', 'Не указан контактный телефон пациента или его представителя.');
        }

        if (blank($data['document_date'] ?? null)) {
            $push($issues, 'document_date', 'Не указана дата направления.');
        }
        if (blank($data['referral_kind'] ?? null)) {
            $push($issues, 'referral_kind', 'Не указан вид направления (первичное/повторное).');
        }
        if (blank($data['examination_format'] ?? null)) {
            $push($issues, 'format', 'Не выбрана форма проведения МСЭ.');
        }
        if (blank($data['protocol_number'] ?? null) || blank($data['protocol_date'] ?? null)) {
            $push($issues, 'protocol', 'Нет номера и/или даты протокола врачебной комиссии.');
        }
        if (blank($data['consent_date'] ?? null)) {
            $push($issues, 'consent_date', 'Не указана дата письменного согласия на направление и проведение МСЭ.');
        }

        $hasConsentDocument = $request->file('consent_document') instanceof UploadedFile
            || ($document?->hasConsentDocument() && !$request->boolean('remove_consent_document'));

        if (!$hasConsentDocument) {
            $push($issues, 'consent_document', 'Не загружено подписанное согласие.');
        }

        $purposes = collect($data['purposes'] ?? [])->filter()->values();
        if ($purposes->isEmpty()) {
            $push($issues, 'purpose', 'Не выбрана цель проведения МСЭ.');
        }

        if (blank($data['observed_since_date'] ?? null)) {
            $push($issues, 'observation', 'Не указано, с какого времени пациент наблюдается в медицинской организации.');
        }
        if (blank($data['disease_history'] ?? null)) {
            $push($issues, 'disease_history', 'Не заполнен анамнез заболевания.');
        }
        if (blank($data['life_history'] ?? null)) {
            $push($issues, 'life_history', 'Не заполнен анамнез жизни.');
        }
        if (blank($data['health_status'] ?? null)) {
            $push($issues, 'mental_status', 'Не описан психический статус и стойкие нарушения функций.');
        }

        if (
            blank($data['height_cm'] ?? null)
            || blank($data['weight_kg'] ?? null)
            || blank($data['bmi'] ?? null)
            || blank($data['body_build'] ?? null)
        ) {
            $push(
                $issues,
                'physical',
                'Не заполнены обязательные физические параметры формы 088/у: рост, масса, ИМТ и телосложение.'
            );
        }

        if ($age !== null && $age < 3) {
            $underThreeMissing = [];
            if (blank($data['birth_weight_g'] ?? null)) {
                $underThreeMissing[] = 'масса тела при рождении (п. 28.7)';
            }
            if (blank($data['physical_development'] ?? null)) {
                $underThreeMissing[] = 'физическое развитие (п. 28.8)';
            }
            if ($underThreeMissing !== []) {
                $push(
                    $issues,
                    'under_three',
                    'Для ребёнка младше 3 лет необходимо заполнить: ' . implode(', ', $underThreeMissing) . '.'
                );
            }
        }

        $examinations = collect($data['examinations'] ?? [])
            ->filter(static fn (mixed $row): bool => is_array($row))
            ->values();

        if ($examinations->isEmpty()) {
            $push($issues, 'examinations', 'Не внесены результаты обязательных обследований для МСЭ.');
        } elseif ($examinations->contains(static function (array $row): bool {
            return blank($row['date'] ?? null)
                || blank($row['name'] ?? null)
                || blank($row['result'] ?? null);
        })) {
            $push(
                $issues,
                'examinations',
                'Есть незаполненная строка обследования: нужны дата, наименование и результат.'
            );
        }

        if (!(bool) ($data['mse_gate_persistent'] ?? false)) {
            $push($issues, 'persistent', 'ВК не подтвердила наличие данных о стойком нарушении функций.');
        }
        if (!(bool) ($data['mse_gate_measures'] ?? false)) {
            $push(
                $issues,
                'measures',
                'Не подтверждено проведение необходимых диагностики, лечения и медицинской реабилитации.'
            );
        }
        if (!(bool) ($data['mse_gate_exams'] ?? false)) {
            $push(
                $issues,
                'required_exams',
                'Не подтверждена проверка возрастного и диагноз-зависимого перечня обследований для направления на МСЭ.'
            );
        }

        if (blank($data['commission_chairman'] ?? null)) {
            $push($issues, 'commission', 'Не указан председатель врачебной комиссии.');
        }

        if ($age !== null && $age >= 18 && $purposes->contains('disabled_child')) {
            $push($issues, 'age_purpose', 'Для пациента 18+ выбрана цель, относящаяся к категории «ребёнок-инвалид».');
        }
        if ($age !== null && $age < 18 && $purposes->contains('disability_group')) {
            $push($issues, 'age_purpose', 'Для пациента младше 18 лет выбрана взрослая цель установления группы инвалидности.');
        }

        return $issues;
    }

    /**
     * Строит список доработок для страницы просмотра уже сохранённого направления.
     * Проверяем именно снимок документа; актуальную карточку пациента используем
     * только там, где врачу нужно понять, что исправлять дальше.
     *
     * @return array<int, array{key: string, message: string}>
     */
    private function msePreparationIssuesForDocument(MseReferral $document): array
    {
        $issues = [];
        $push = static function (array &$target, string $key, string $message): void {
            $target[] = ['key' => $key, 'message' => $message];
        };
        $details = (array) ($document->details ?: []);
        $snapshot = (array) ($document->patient_snapshot ?: []);

        if ($document->status !== MseReferral::STATUS_PREPARED && $document->patient) {
            $missingProfileFields = $this->msePatientMissingProfileFields($document->patient);
            if ($missingProfileFields !== []) {
                $push(
                    $issues,
                    'patient_profile',
                    'В карточке пациента нужно заполнить: ' . implode(', ', $missingProfileFields) . '.'
                );
            }
        }

        $diagnosisCode = trim((string) $document->diagnosis_code);
        $diagnosisTitle = trim((string) $document->diagnosis_title);
        if ($diagnosisCode === '' && $diagnosisTitle === '') {
            $currentCode = trim((string) ($document->patient?->diagnose?->code ?? ''));
            $currentTitle = trim((string) (
                $document->patient?->diagnose?->title
                ?? $document->patient?->diagnose?->name
                ?? ''
            ));

            $message = ($currentCode !== '' || $currentTitle !== '')
                ? 'В направлении ещё не зафиксирован основной диагноз. В карточке пациента диагноз уже есть — откройте редактирование и сохраните направление.'
                : 'В карте пациента не установлен основной диагноз. Сначала укажите диагноз, затем обновите направление.';
            $push($issues, 'diagnosis', $message);
        } elseif ($diagnosisCode === '') {
            $push($issues, 'diagnosis_code', 'В направлении у основного диагноза отсутствует код МКБ.');
        }

        $age = data_get($snapshot, 'age');
        if (!is_numeric($age) && filled(data_get($snapshot, 'birth_date'))) {
            try {
                $age = Carbon::parse(data_get($snapshot, 'birth_date'))->age;
            } catch (Throwable) {
                $age = null;
            }
        }
        $age = is_numeric($age) ? (int) $age : null;

        if ($age === null) {
            $push($issues, 'age', 'Не удалось определить возраст пациента. Проверьте дату рождения.');
        }

        $citizenship = trim((string) data_get($snapshot, 'citizenship.type'));
        if ($citizenship === '') {
            $push($issues, 'citizenship', 'Не указано гражданство пациента.');
        }

        if (blank(data_get($snapshot, 'sex'))) {
            $push($issues, 'patient_sex', 'Не указан пол пациента.');
        }
        if (blank(data_get($snapshot, 'snils'))) {
            $push($issues, 'patient_snils', 'Не указан СНИЛС пациента.');
        }
        if (!(bool) data_get($snapshot, 'homeless', false) && blank(data_get($snapshot, 'address'))) {
            $push($issues, 'patient_address', 'Не указан адрес места жительства/пребывания пациента.');
        }

        $identityMissing = [];
        foreach ([
                     'identity.name' => 'наименование документа',
                     'identity.number' => 'номер документа',
                     'identity.issued_by' => 'кем выдан документ',
                     'identity.issue_date' => 'дата выдачи документа',
                 ] as $path => $label) {
            if (blank(data_get($snapshot, $path))) {
                $identityMissing[] = $label;
            }
        }
        if ($citizenship === 'russian' && blank(data_get($snapshot, 'identity.series'))) {
            $identityMissing[] = 'серия документа';
        }
        if ($identityMissing !== []) {
            $identityKind = $citizenship === 'russian' && $age !== null && $age < 14
                ? 'свидетельства о рождении'
                : ($citizenship === 'russian' && $age !== null && $age >= 14
                    ? 'паспорта'
                    : 'документа, удостоверяющего личность');
            $push(
                $issues,
                'identity',
                'Не полностью заполнены реквизиты ' . $identityKind . ': ' . implode(', ', $identityMissing) . '.'
            );
        }

        $representativePaths = [
            'representative.full_name' => 'ФИО представителя',
            'representative.birth_date' => 'дата рождения представителя',
            'representative.authority_document' => 'документ о полномочиях представителя',
            'representative.identity' => 'документ, удостоверяющий личность представителя',
            'representative.phone' => 'телефон представителя',
            'representative.snils' => 'СНИЛС представителя',
        ];
        $hasAnyRepresentativeData = collect(array_keys($representativePaths))
            ->contains(static fn (string $path): bool => filled(data_get($snapshot, $path)));

        if ($age !== null && $age < 15) {
            $representativeMissing = collect($representativePaths)
                ->filter(static fn (string $label, string $path): bool => blank(data_get($snapshot, $path)))
                ->values()
                ->all();
            if ($representativeMissing !== []) {
                $push(
                    $issues,
                    'representative',
                    'Для пациента младше 15 лет в психиатрическом сценарии необходимо заполнить сведения законного представителя: '
                    . implode(', ', $representativeMissing) . '.'
                );
            }
        } elseif ($hasAnyRepresentativeData) {
            $representativeMissing = collect($representativePaths)
                ->filter(static fn (string $label, string $path): bool => blank(data_get($snapshot, $path)))
                ->values()
                ->all();
            if ($representativeMissing !== []) {
                $push($issues, 'representative', 'Блок представителя заполнен не полностью: ' . implode(', ', $representativeMissing) . '.');
            }
        }

        $patientContactPhone = trim((string) data_get($snapshot, 'phone'));
        $representativeContactPhone = trim((string) data_get($snapshot, 'representative.phone'));
        if ($patientContactPhone === '' && $representativeContactPhone === '') {
            $push($issues, 'contact', 'Не указан контактный телефон пациента или его представителя.');
        }

        if (!$document->document_date) {
            $push($issues, 'document_date', 'Не указана дата направления.');
        }
        if (blank($document->referral_kind)) {
            $push($issues, 'referral_kind', 'Не указан вид направления (первичное/повторное).');
        }
        if (blank($document->examination_format)) {
            $push($issues, 'format', 'Не выбрана форма проведения МСЭ.');
        }
        if (blank($document->protocol_number) || !$document->protocol_date) {
            $push($issues, 'protocol', 'Нет номера и/или даты протокола врачебной комиссии.');
        }
        if (!$document->consent_date) {
            $push($issues, 'consent_date', 'Не указана дата письменного согласия на направление и проведение МСЭ.');
        }
        if (!$document->hasConsentDocument()) {
            $push($issues, 'consent_document', 'Не загружено подписанное согласие.');
        }

        $purposes = collect($document->purposes ?? [])->filter()->values();
        if ($purposes->isEmpty()) {
            $push($issues, 'purpose', 'Не выбрана цель проведения МСЭ.');
        }

        if (blank(data_get($details, 'clinical.observed_since_date'))) {
            $push($issues, 'observation', 'Не указано, с какого времени пациент наблюдается в медицинской организации.');
        }
        if (blank(data_get($details, 'clinical.disease_history'))) {
            $push($issues, 'disease_history', 'Не заполнен анамнез заболевания.');
        }
        if (blank(data_get($details, 'clinical.life_history'))) {
            $push($issues, 'life_history', 'Не заполнен анамнез жизни.');
        }
        if (blank(data_get($details, 'clinical.health_status'))) {
            $push($issues, 'mental_status', 'Не описан психический статус и стойкие нарушения функций.');
        }

        if (
            blank(data_get($details, 'clinical.anthropometry.height_cm'))
            || blank(data_get($details, 'clinical.anthropometry.weight_kg'))
            || blank(data_get($details, 'clinical.anthropometry.bmi'))
            || blank(data_get($details, 'clinical.anthropometry.body_build'))
        ) {
            $push(
                $issues,
                'physical',
                'Не заполнены обязательные физические параметры формы 088/у: рост, масса, ИМТ и телосложение.'
            );
        }

        if ($age !== null && $age < 3) {
            $underThreeMissing = [];
            if (blank(data_get($details, 'clinical.anthropometry.birth_weight_g'))) {
                $underThreeMissing[] = 'масса тела при рождении (п. 28.7)';
            }
            if (blank(data_get($details, 'clinical.anthropometry.physical_development'))) {
                $underThreeMissing[] = 'физическое развитие (п. 28.8)';
            }
            if ($underThreeMissing !== []) {
                $push(
                    $issues,
                    'under_three',
                    'Для ребёнка младше 3 лет необходимо заполнить: ' . implode(', ', $underThreeMissing) . '.'
                );
            }
        }

        $examinations = collect(data_get($details, 'clinical.examinations', []))
            ->filter(static fn (mixed $row): bool => is_array($row))
            ->values();

        if ($examinations->isEmpty()) {
            $push($issues, 'examinations', 'Не внесены результаты обязательных обследований для МСЭ.');
        } elseif ($examinations->contains(static function (array $row): bool {
            return blank($row['date'] ?? null)
                || blank($row['name'] ?? null)
                || blank($row['result'] ?? null);
        })) {
            $push(
                $issues,
                'examinations',
                'Есть незаполненная строка обследования: нужны дата, наименование и результат.'
            );
        }

        /*
         * Старые уже оформленные документы могли быть созданы до появления
         * этих трёх внутренних подтверждений. Не помечаем их задним числом
         * как ошибочные. Для черновиков и новых документов проверки обязательны.
         */
        $storedGate = data_get($details, 'mse_gate');
        if ($document->status !== MseReferral::STATUS_PREPARED || is_array($storedGate)) {
            if (!(bool) data_get($details, 'mse_gate.persistent', false)) {
                $push($issues, 'persistent', 'ВК не подтвердила наличие данных о стойком нарушении функций.');
            }
            if (!(bool) data_get($details, 'mse_gate.measures', false)) {
                $push(
                    $issues,
                    'measures',
                    'Не подтверждено проведение необходимых диагностики, лечения и медицинской реабилитации.'
                );
            }
            if (!(bool) data_get($details, 'mse_gate.exams', false)) {
                $push(
                    $issues,
                    'required_exams',
                    'Не подтверждена проверка возрастного и диагноз-зависимого перечня обследований для направления на МСЭ.'
                );
            }
        }

        if (blank(data_get($details, 'commission.chairman'))) {
            $push($issues, 'commission', 'Не указан председатель врачебной комиссии.');
        }

        if ($age !== null && $age >= 18 && $purposes->contains('disabled_child')) {
            $push($issues, 'age_purpose', 'Для пациента 18+ выбрана цель, относящаяся к категории «ребёнок-инвалид».');
        }
        if ($age !== null && $age < 18 && $purposes->contains('disability_group')) {
            $push($issues, 'age_purpose', 'Для пациента младше 18 лет выбрана взрослая цель установления группы инвалидности.');
        }

        return $issues;
    }

    private function findPatientOrFail(int $patientId, ?Doctor $doctor): Patient
    {
        $patient = $this->patientQuery($doctor)->find($patientId);

        if (!$patient) {
            throw ValidationException::withMessages([
                'patient_id' => 'Пациент не найден или недоступен для вашей клиники.',
            ]);
        }

        return $patient;
    }

    private function patientQuery(?Doctor $doctor): Builder
    {
        return Patient::query()
            ->with('diagnose')
            ->when(
                !$this->isDebug(),
                static fn (Builder $query) => $query->where('clinic_id', $doctor?->clinic_id)
            );
    }

    private function authorizeDocument(MseReferral $document): void
    {
        if ($this->isDebug()) {
            return;
        }

        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        if ((int) $document->clinic_id !== (int) $doctor?->clinic_id) {
            abort(403);
        }
    }

    private function ensureDoctorAvailable(?Doctor $doctor): void
    {
        if (!$this->isDebug() && (!$doctor || !$doctor->clinic_id)) {
            abort(403, 'Для работы с направлениями на МСЭ требуется профиль врача и клиника.');
        }
    }

    private function currentDoctor(): ?Doctor
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'doctor')) {
            return null;
        }

        return $user->doctor()->first();
    }

    private function patientBirthDate(Patient $patient): ?string
    {
        $value = $patient->getRawOriginal('birth_at') ?: $patient->birth_at;

        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function patientAge(Patient $patient): ?int
    {
        $birthDate = $this->patientBirthDate($patient);

        if (!$birthDate) {
            return null;
        }

        try {
            return Carbon::parse($birthDate)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    private function patientAgeMonths(Patient $patient): ?int
    {
        $birthDate = $this->patientBirthDate($patient);

        if (!$birthDate) {
            return null;
        }

        try {
            $birth = Carbon::parse($birthDate)->startOfDay();
            $today = today()->startOfDay();

            if ($birth->greaterThan($today)) {
                return null;
            }

            return $birth->diffInMonths($today);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Возрастной профиль не заменяет проверку приказа 259н/238н по конкретному
     * диагнозу. Он определяет, какой возрастной раздел документов и формы 088/у
     * должен применяться.
     *
     * @return array<string, mixed>
     */
    private function mseAgeDocumentationProfileForDocument(MseReferral $document): array
    {
        $snapshot = (array) ($document->patient_snapshot ?: []);
        $age = data_get($snapshot, 'age');
        $ageMonths = data_get($snapshot, 'age_months');

        if (!is_numeric($age) && filled(data_get($snapshot, 'birth_date'))) {
            try {
                $birth = Carbon::parse(data_get($snapshot, 'birth_date'))->startOfDay();
                $age = $birth->age;
                $ageMonths = $birth->diffInMonths(today()->startOfDay());
            } catch (Throwable) {
                $age = null;
                $ageMonths = null;
            }
        }

        return $this->mseAgeDocumentationProfile(
            is_numeric($age) ? (int) $age : null,
            is_numeric($ageMonths) ? (int) $ageMonths : null,
            trim((string) data_get($snapshot, 'citizenship.type'))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mseAgeDocumentationProfile(?int $age, ?int $ageMonths, string $citizenship): array
    {
        if ($age === null) {
            return [
                'key' => 'unknown',
                'title' => 'Возраст не определён',
                'age_label' => 'Проверьте дату рождения',
                'mse_section' => 'Нельзя выбрать возрастной раздел приказа № 259н/238н.',
                'identity' => 'Нельзя определить возрастной тип документа.',
                'consent' => 'Нельзя определить возрастной сценарий согласия.',
                'psychiatrist' => 'Нельзя определить возрастную специализацию врача.',
                'special_fields' => [],
            ];
        }

        $isRussian = $citizenship === 'russian';
        $identity = !$isRussian
            ? 'Документ, удостоверяющий личность иностранного гражданина или лица без гражданства.'
            : ($age < 14
                ? 'Для гражданина РФ до 14 лет — свидетельство о рождении (либо иной предусмотренный документ).'
                : 'Для гражданина РФ с 14 лет — паспорт гражданина РФ (либо иной предусмотренный документ).');

        $ageLabel = $age === 0 && $ageMonths !== null
            ? $ageMonths . ' полн. мес.'
            : $age . ' полн. лет';

        if ($age < 1) {
            return [
                'key' => 'infant',
                'title' => 'Ребёнок до 1 года',
                'age_label' => $ageLabel,
                'mse_section' => 'Раздел II приказа № 259н/238н — обследования для лиц до 18 лет.',
                'identity' => $identity,
                'consent' => 'Психиатрическая помощь/освидетельствование — через родителя или иного законного представителя; блок представителя формы 088/у обязателен для этого сценария.',
                'psychiatrist' => 'Детский врач-психиатр; диагноз-зависимые обследования проверяются по детскому разделу.',
                'special_fields' => [
                    'В п. 7 формы 088/у возраст указывается в полных месяцах.',
                    'Обязательны масса тела при рождении (п. 28.7) и физическое развитие (п. 28.8).',
                ],
            ];
        }

        if ($age < 3) {
            return [
                'key' => 'under3',
                'title' => 'Ребёнок 1–2 лет',
                'age_label' => $ageLabel,
                'mse_section' => 'Раздел II приказа № 259н/238н — обследования для лиц до 18 лет.',
                'identity' => $identity,
                'consent' => 'Психиатрическая помощь/освидетельствование — через родителя или иного законного представителя; блок представителя формы 088/у обязателен для этого сценария.',
                'psychiatrist' => 'Детский врач-психиатр; диагноз-зависимые обследования проверяются по детскому разделу.',
                'special_fields' => [
                    'Обязательны масса тела при рождении (п. 28.7) и физическое развитие (п. 28.8).',
                ],
            ];
        }

        if ($age < 14) {
            return [
                'key' => 'child_under14',
                'title' => 'Ребёнок 3–13 лет',
                'age_label' => $ageLabel,
                'mse_section' => 'Раздел II приказа № 259н/238н — обследования для лиц до 18 лет.',
                'identity' => $identity,
                'consent' => 'До 15 лет в психиатрическом сценарии права и согласие реализует родитель или иной законный представитель.',
                'psychiatrist' => 'Детский врач-психиатр; диагноз-зависимые обследования проверяются по детскому разделу.',
                'special_fields' => [
                    'Сведения об образовании заполняются, если ребёнок обучается; для дошкольника учитывается возрастная группа.',
                ],
            ];
        }

        if ($age < 15) {
            return [
                'key' => 'age14',
                'title' => 'Подросток 14 лет',
                'age_label' => $ageLabel,
                'mse_section' => 'Раздел II приказа № 259н/238н — обследования для лиц до 18 лет.',
                'identity' => $identity,
                'consent' => 'Возраст уже паспортный, но в психиатрическом сценарии до 15 лет согласие/представительство остаётся за родителем или иным законным представителем.',
                'psychiatrist' => 'Детский врач-психиатр; диагноз-зависимые обследования проверяются по детскому разделу.',
                'special_fields' => [
                    'Цель установления инвалидности — категория «ребёнок-инвалид», а не взрослая группа инвалидности.',
                ],
            ];
        }

        if ($age < 18) {
            return [
                'key' => 'adolescent',
                'title' => 'Подросток 15–17 лет',
                'age_label' => $ageLabel,
                'mse_section' => 'Раздел II приказа № 259н/238н — обследования для лиц до 18 лет.',
                'identity' => $identity,
                'consent' => 'По одному лишь возрасту законный представитель уже не обязателен для добровольной психиатрической помощи; если представитель участвует, п. 17 формы 088/у заполняется полностью.',
                'psychiatrist' => 'Детский/подростковый врач-психиатр согласно возрасту и организации; диагноз-зависимые обследования — по детскому разделу.',
                'special_fields' => [
                    'Цель установления инвалидности — категория «ребёнок-инвалид», а не взрослая группа инвалидности.',
                ],
            ];
        }

        return [
            'key' => 'adult',
            'title' => 'Совершеннолетний пациент',
            'age_label' => $ageLabel,
            'mse_section' => 'Раздел I приказа № 259н/238н — обследования для лиц 18 лет и старше.',
            'identity' => $identity,
            'consent' => 'Согласие оформляет сам гражданин; представитель — только при наличии законных/уполномоченных оснований.',
            'psychiatrist' => 'Врач-психиатр взрослой сети; диагноз-зависимые обследования проверяются по взрослому разделу.',
            'special_fields' => [
                'Для установления инвалидности используется взрослая цель «Установление группы инвалидности».',
            ],
        ];
    }

    private function formatDateInput(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatDateDisplay(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function storeConsentDocument(
        MseReferral $document,
        UploadedFile $file,
        ?Doctor $doctor
    ): array {
        $disk = MseReferral::CONSENT_DOCUMENT_DISK;
        $extension = mb_strtolower(trim((string) ($file->extension() ?: 'bin')));
        $storedName = Str::uuid()->toString() . '.' . $extension;
        $clinicDirectory = $document->clinic_id ?: $doctor?->clinic_id ?: 0;
        $directory = sprintf(
            'mse/consent-documents/clinic-%s/referral-%d',
            $clinicDirectory,
            $document->id
        );
        $path = $file->storeAs($directory, $storedName, $disk);

        if (!$path) {
            throw new RuntimeException('Не удалось сохранить подписанное разрешение на проведение МСЭ.');
        }

        $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $originalName = Str::limit(trim($originalName), 255, '');

        return [
            'consent_document_disk' => $disk,
            'consent_document_path' => $path,
            'consent_document_original_name' => $originalName !== ''
                ? $originalName
                : 'Разрешение на проведение МСЭ.' . $extension,
            'consent_document_mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'consent_document_size' => (int) $file->getSize(),
            'consent_document_uploaded_at' => now(),
            'consent_document_uploaded_by_doctor_id' => $doctor?->id,
        ];
    }

    /**
     * @return array<string, null>
     */
    private function emptyConsentDocumentPayload(): array
    {
        return [
            'consent_document_disk' => null,
            'consent_document_path' => null,
            'consent_document_original_name' => null,
            'consent_document_mime_type' => null,
            'consent_document_size' => null,
            'consent_document_uploaded_at' => null,
            'consent_document_uploaded_by_doctor_id' => null,
        ];
    }

    /**
     * @return array{disk: string, path: string}|null
     */
    private function consentDocumentStorageReference(MseReferral $document): ?array
    {
        if (!$document->hasConsentDocument()) {
            return null;
        }

        return [
            'disk' => $document->consentDocumentDisk(),
            'path' => (string) $document->consent_document_path,
        ];
    }

    /**
     * @param array<string, mixed>|null $reference
     */
    private function deleteConsentDocumentFromStorage(?array $reference): void
    {
        $path = trim((string) (
            $reference['path']
            ?? $reference['consent_document_path']
            ?? ''
        ));

        if ($path === '') {
            return;
        }

        $disk = (string) (
            $reference['disk']
            ?? $reference['consent_document_disk']
            ?? MseReferral::CONSENT_DOCUMENT_DISK
        );

        try {
            Storage::disk($disk)->delete($path);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function isDebug(): bool
    {
        return method_exists(app(), 'hasDebugModeEnabled')
            ? app()->hasDebugModeEnabled()
            : (bool) config('app.debug');
    }

    private function isIsoDate(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }
}
