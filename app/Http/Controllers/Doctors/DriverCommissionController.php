<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDriverPsychiatricExaminationRequest;
use App\Models\Doctor;
use App\Models\DriverPsychiatricExamination;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DriverCommissionController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $baseQuery = DriverPsychiatricExamination::query()
            ->with(['patient.diagnose', 'creator'])
            ->when(
                !$this->isDebug(),
                static fn (Builder $query) => $query->where('clinic_id', $doctor?->clinic_id)
            );

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)
                ->where('status', DriverPsychiatricExamination::STATUS_DRAFT)
                ->count(),
            'certificates' => (clone $baseQuery)
                ->where('status', DriverPsychiatricExamination::STATUS_PREPARED)
                ->whereIn('result', [
                    DriverPsychiatricExamination::RESULT_NO_CONTRAINDICATIONS,
                    DriverPsychiatricExamination::RESULT_COMMISSION_NO_CONTRAINDICATIONS,
                ])
                ->count(),
            'referred' => (clone $baseQuery)
                ->where('result', DriverPsychiatricExamination::RESULT_REFER_COMMISSION)
                ->count(),
        ];

        $documents = $baseQuery
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = trim($request->string('q')->toString());
                $like = '%' . $term . '%';

                $query->where(function (Builder $query) use ($like): void {
                    $query
                        ->where('document_number', 'ilike', $like)
                        ->orWhere('certificate_number', 'ilike', $like)
                        ->orWhere('diagnosis_code', 'ilike', $like)
                        ->orWhere('diagnosis_title', 'ilike', $like)
                        ->orWhere('conclusion_text', 'ilike', $like)
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
                    && array_key_exists($request->string('status')->toString(), DriverPsychiatricExamination::statusOptions()),
                static fn (Builder $query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('result')
                    && array_key_exists($request->string('result')->toString(), DriverPsychiatricExamination::resultOptions()),
                static fn (Builder $query) => $query->where('result', $request->string('result')->toString())
            )
            ->when(
                $request->filled('category')
                    && array_key_exists($request->string('category')->toString(), DriverPsychiatricExamination::vehicleCategoryOptions()),
                static fn (Builder $query) => $query->whereJsonContains('vehicle_categories', $request->string('category')->toString())
            )
            ->when(
                $this->isIsoDate($request->input('date_from')),
                static fn (Builder $query) => $query->whereDate('examination_date', '>=', $request->input('date_from'))
            )
            ->when(
                $this->isIsoDate($request->input('date_to')),
                static fn (Builder $query) => $query->whereDate('examination_date', '<=', $request->input('date_to'))
            )
            ->orderByDesc('examination_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('doctors.commissions.driver.index', [
            'documents' => $documents,
            'stats' => $stats,
            'statusOptions' => DriverPsychiatricExamination::statusOptions(),
            'resultOptions' => DriverPsychiatricExamination::resultOptions(),
            'contraindicationAssessmentOptions' => DriverPsychiatricExamination::contraindicationAssessmentOptions(),
            'vehicleCategoryOptions' => DriverPsychiatricExamination::vehicleCategoryOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $document = new DriverPsychiatricExamination([
            'examination_date' => today(),
            'status' => DriverPsychiatricExamination::STATUS_DRAFT,
            'purpose' => DriverPsychiatricExamination::PURPOSE_CANDIDATE,
            'vehicle_categories' => [],
            'risk_flags' => [],
        ]);

        $patient = null;
        if ($request->integer('patient_id')) {
            $patient = $this->patientQuery($doctor)->find($request->integer('patient_id'));
        }

        $document->setRelation('patient', $patient);

        return $this->formView($document, false, $doctor);
    }

    public function store(SaveDriverPsychiatricExaminationRequest $request): RedirectResponse
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);
        $data = $request->validated();
        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);

        $document = DB::transaction(function () use ($data, $doctor, $patient): DriverPsychiatricExamination {
            $document = DriverPsychiatricExamination::query()->create(
                $this->documentPayload($data, $patient, $doctor)
            );

            $document->update([
                'document_number' => sprintf(
                    'ВОД-ПСИХ-%s-%06d',
                    Carbon::parse($document->examination_date)->format('Y'),
                    $document->id
                ),
            ]);

            $this->syncCertificateNumber($document);

            return $document->refresh();
        });

        return redirect()
            ->route('doctors.commissions.driver.show', $document)
            ->with('status', 'success')
            ->with('message', 'Психиатрическое обследование для водительской комиссии создано.');
    }

    public function show(DriverPsychiatricExamination $driverCommission): View
    {
        $this->authorizeDocument($driverCommission);
        $driverCommission->load(['patient.diagnose', 'creator']);

        return view('doctors.commissions.driver.show', [
            'document' => $driverCommission,
            'riskFlagOptions' => DriverPsychiatricExamination::riskFlagOptions(),
            'fContraindicationGroups' => DriverPsychiatricExamination::drivingContraindicationGroups(),
            'fContraindicationMatch' => DriverPsychiatricExamination::drivingContraindicationMatch(
                $driverCommission->diagnosis_code
            ),
            'driverOutputBlocked' => $driverCommission->isDrivingDocumentOutputBlocked(),
            'driverHardBlocked' => $driverCommission->hasConfirmedDrivingContraindication(),
            'driverBlockReason' => $driverCommission->drivingDocumentBlockReason(),
        ]);
    }

    public function edit(DriverPsychiatricExamination $driverCommission): View|RedirectResponse
    {
        $this->authorizeDocument($driverCommission);
        $driverCommission->load(['patient.diagnose', 'creator']);

        if ($driverCommission->hasConfirmedDrivingContraindication()) {
            return redirect()
                ->route('doctors.commissions.driver.show', $driverCommission)
                ->with('status', 'error')
                ->with('message', 'Редактирование заблокировано: медицинское противопоказание к управлению транспортными средствами подтверждено.');
        }

        return $this->formView($driverCommission, true, $this->currentDoctor());
    }

    public function update(
        SaveDriverPsychiatricExaminationRequest $request,
        DriverPsychiatricExamination $driverCommission
    ): RedirectResponse {
        $this->authorizeDocument($driverCommission);

        if ($driverCommission->hasConfirmedDrivingContraindication()) {
            return redirect()
                ->route('doctors.commissions.driver.show', $driverCommission)
                ->with('status', 'error')
                ->with('message', 'Сохранение заблокировано: медицинское противопоказание к управлению транспортными средствами подтверждено.');
        }

        $doctor = $this->currentDoctor();
        $data = $request->validated();
        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);

        DB::transaction(function () use ($data, $doctor, $patient, $driverCommission): void {
            $driverCommission->update(
                $this->documentPayload($data, $patient, $doctor, $driverCommission)
            );

            $this->syncCertificateNumber($driverCommission);
        });

        return redirect()
            ->route('doctors.commissions.driver.show', $driverCommission)
            ->with('status', 'success')
            ->with('message', 'Изменения сохранены.');
    }

    public function destroy(DriverPsychiatricExamination $driverCommission): RedirectResponse
    {
        $this->authorizeDocument($driverCommission);

        if ($driverCommission->status === DriverPsychiatricExamination::STATUS_PREPARED) {
            return redirect()
                ->route('doctors.commissions.driver.index')
                ->with('status', 'error')
                ->with('message', 'Оформленный документ нельзя удалить. Сначала верните его в черновик.');
        }

        $driverCommission->delete();

        return redirect()
            ->route('doctors.commissions.driver.index')
            ->with('status', 'success')
            ->with('message', 'Черновик удалён.');
    }

    public function printBlank(Request $request): View|RedirectResponse
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $patientId = $request->integer('patient_id');
        if (!$patientId) {
            return redirect()
                ->route('doctors.commissions.driver.create')
                ->with('status', 'error')
                ->with('message', 'Для печати пустого бланка сначала выберите пациента. Данные пациента в бланк не подставляются — выбор нужен только для проверки противопоказаний.');
        }

        $patient = $this->findPatientOrFail($patientId, $doctor);
        $match = DriverPsychiatricExamination::drivingContraindicationMatch($patient->diagnose?->code);
        $assessment = trim((string) $request->input('contraindication_assessment'));

        if ($match !== null && $assessment === '') {
            return redirect()
                ->route('doctors.commissions.driver.create', ['patient_id' => $patient->id])
                ->with('status', 'error')
                ->with('message', 'Печать заблокирована: сначала выполните клиническую оценку условия противопоказания по перечню № 892-р.');
        }

        if ($assessment === DriverPsychiatricExamination::CONTRA_ASSESSMENT_CONFIRMED) {
            return redirect()
                ->route('doctors.commissions.driver.create', ['patient_id' => $patient->id])
                ->with('status', 'error')
                ->with('message', 'Печать заблокирована: медицинское противопоказание к управлению транспортными средствами подтверждено.');
        }

        $doctor?->loadMissing('clinic');
        $clinic = $doctor?->clinic;

        $organizationName = trim((string) (
            data_get($clinic, 'name')
            ?? data_get($clinic, 'title')
            ?? data_get($doctor, 'organization_name')
            ?? config('driver_commission.print.organization_name')
            ?? config('app.name')
        ));

        $organizationAddress = trim((string) (
            data_get($clinic, 'address')
            ?? data_get($clinic, 'actual_address')
            ?? data_get($clinic, 'legal_address')
            ?? data_get($doctor, 'address_job')
            ?? data_get($doctor, 'work_address')
            ?? data_get($doctor, 'address')
            ?? config('driver_commission.print.organization_address')
            ?? ''
        ));

        return view('doctors.commissions.driver.manual-print', [
            'purposeOptions' => DriverPsychiatricExamination::purposeOptions(),
            'vehicleCategoryOptions' => DriverPsychiatricExamination::vehicleCategoryOptions(),
            'riskFlagOptions' => DriverPsychiatricExamination::riskFlagOptions(),
            'organizationName' => $organizationName,
            'organizationAddress' => $organizationAddress,
        ]);
    }

    public function print(DriverPsychiatricExamination $driverCommission): View|RedirectResponse
    {
        $this->authorizeDocument($driverCommission);
        $driverCommission->load(['patient.diagnose', 'creator']);

        if ($driverCommission->isDrivingDocumentOutputBlocked()) {
            return redirect()
                ->route('doctors.commissions.driver.show', $driverCommission)
                ->with('status', 'error')
                ->with('message', $driverCommission->drivingDocumentBlockReason() ?? 'Печать водительского документа заблокирована.');
        }

        return view('doctors.commissions.driver.print', [
            'document' => $driverCommission,
        ]);
    }

    private function formView(
        DriverPsychiatricExamination $document,
        bool $isEditing,
        ?Doctor $doctor
    ): View {
        $document->loadMissing('patient.diagnose');

        $selectedPatientId = old('patient_id', $document->patient_id);
        $patient = $document->patient;

        if ($selectedPatientId && (int) $selectedPatientId !== (int) $patient?->id) {
            $patient = $this->patientQuery($doctor)->find((int) $selectedPatientId);
        }

        $initialPatient = $this->buildPatientPayload($patient, $document, $isEditing);

        return view('doctors.commissions.driver.form', [
            'document' => $document,
            'isEditing' => $isEditing,
            'pageTitle' => $isEditing
                ? 'Редактирование водительского психиатрического обследования'
                : 'Новое обследование для водительской комиссии',
            'headingTitle' => $isEditing
                ? ($document->document_number ?: 'Редактирование обследования')
                : 'Новое психиатрическое обследование',
            'selectedPatientId' => $selectedPatientId,
            'initialPatient' => $initialPatient,
            'statusOptions' => DriverPsychiatricExamination::statusOptions(),
            'purposeOptions' => DriverPsychiatricExamination::purposeOptions(),
            'vehicleCategoryOptions' => DriverPsychiatricExamination::vehicleCategoryOptions(),
            'vehicleCategoryDescriptions' => DriverPsychiatricExamination::vehicleCategoryDescriptions(),
            'resultOptions' => DriverPsychiatricExamination::resultOptions(),
            'contraindicationAssessmentOptions' => DriverPsychiatricExamination::contraindicationAssessmentOptions(),
            'riskFlagOptions' => DriverPsychiatricExamination::riskFlagOptions(),
            'fContraindicationGroups' => DriverPsychiatricExamination::drivingContraindicationGroups(),
            'fContraindicationMatch' => DriverPsychiatricExamination::drivingContraindicationMatch(
                data_get($initialPatient, 'diagnosis_code')
            ),
            'formValues' => $this->buildFormValues($document),
        ]);
    }

    private function documentPayload(
        array $data,
        Patient $patient,
        ?Doctor $doctor,
        ?DriverPsychiatricExamination $document = null
    ): array {
        $patientChanged = !$document || (int) $document->patient_id !== (int) $patient->id;
        $keepPreparedSnapshot = $document
            && !$patientChanged
            && $document->status === DriverPsychiatricExamination::STATUS_PREPARED
            && $data['status'] === DriverPsychiatricExamination::STATUS_PREPARED;

        $diagnosisCode = $keepPreparedSnapshot
            ? $document->diagnosis_code
            : ($patient->diagnose?->code ?: null);
        $diagnosisTitle = $keepPreparedSnapshot
            ? $document->diagnosis_title
            : ($patient->diagnose?->title ?? $patient->diagnose?->name);

        $patientSnapshot = $keepPreparedSnapshot
            ? (array) ($document->patient_snapshot ?: [])
            : $this->buildPatientSnapshot($patient);

        $preparedAt = $document?->prepared_at;
        if ($data['status'] === DriverPsychiatricExamination::STATUS_PREPARED && !$preparedAt) {
            $preparedAt = now();
        } elseif ($data['status'] !== DriverPsychiatricExamination::STATUS_PREPARED) {
            $preparedAt = null;
        }

        $payload = [
            'patient_id' => $patient->id,
            'examination_date' => $data['examination_date'],
            'status' => $data['status'],
            'purpose' => $data['purpose'],
            'contraindication_assessment' => $data['contraindication_assessment'] ?? null,
            'vehicle_categories' => array_values($data['vehicle_categories'] ?? []),
            'diagnosis_code' => $diagnosisCode,
            'diagnosis_title' => $diagnosisTitle,
            'patient_snapshot' => $patientSnapshot,
            'complaints' => $data['complaints'] ?? null,
            'disease_history' => $data['disease_history'] ?? null,
            'psychiatric_history' => $data['psychiatric_history'] ?? null,
            'hospitalizations' => $data['hospitalizations'] ?? null,
            'current_treatment' => $data['current_treatment'] ?? null,
            'mental_status' => $data['mental_status'] ?? null,
            'cognitive_behavioral_features' => $data['cognitive_behavioral_features'] ?? null,
            'risk_flags' => array_values($data['risk_flags'] ?? []),
            'medical_record_reviewed' => (bool) ($data['medical_record_reviewed'] ?? false),
            'result' => $data['result'] ?? null,
            'result_rationale' => $data['result_rationale'] ?? null,
            'commission_protocol_number' => $data['commission_protocol_number'] ?? null,
            'commission_protocol_date' => $data['commission_protocol_date'] ?? null,
            'commission_decision' => $data['commission_decision'] ?? null,
            'commission_refusal' => (bool) ($data['commission_refusal'] ?? false),
            'conclusion_text' => $data['conclusion_text'] ?? null,
            'prepared_at' => $preparedAt,
        ];

        if ($data['status'] !== DriverPsychiatricExamination::STATUS_PREPARED) {
            $payload['certificate_number'] = null;
            $payload['certificate_date'] = null;
        }

        if (!$document) {
            $payload['created_by_doctor_id'] = $doctor?->id;
            $payload['clinic_id'] = $doctor?->clinic_id;
        }

        return $payload;
    }

    private function syncCertificateNumber(DriverPsychiatricExamination $document): void
    {
        $document->refresh();

        if (
            $document->status !== DriverPsychiatricExamination::STATUS_PREPARED
            || !$document->isCertificateResult()
            || $document->isDrivingDocumentOutputBlocked()
        ) {
            if ($document->certificate_number || $document->certificate_date) {
                $document->update([
                    'certificate_number' => null,
                    'certificate_date' => null,
                ]);
            }
            return;
        }

        $updates = [];
        if (!$document->certificate_number) {
            $updates['certificate_number'] = sprintf(
                'ПСИХ-ВУ-%s-%06d',
                Carbon::parse($document->examination_date)->format('Y'),
                $document->id
            );
        }
        if (!$document->certificate_date) {
            $updates['certificate_date'] = $document->examination_date;
        }

        if ($updates !== []) {
            $document->update($updates);
        }
    }

    private function buildFormValues(DriverPsychiatricExamination $document): array
    {
        return [
            'examination_date' => $document->examination_date?->format('Y-m-d') ?: today()->format('Y-m-d'),
            'status' => $document->status ?: DriverPsychiatricExamination::STATUS_DRAFT,
            'purpose' => $document->purpose ?: DriverPsychiatricExamination::PURPOSE_CANDIDATE,
            'contraindication_assessment' => $document->contraindication_assessment,
            'vehicle_categories' => $document->vehicle_categories ?: [],
            'complaints' => $document->complaints,
            'disease_history' => $document->disease_history,
            'psychiatric_history' => $document->psychiatric_history,
            'hospitalizations' => $document->hospitalizations,
            'current_treatment' => $document->current_treatment,
            'mental_status' => $document->mental_status,
            'cognitive_behavioral_features' => $document->cognitive_behavioral_features,
            'risk_flags' => $document->risk_flags ?: [],
            'medical_record_reviewed' => (bool) $document->medical_record_reviewed,
            'result' => $document->result,
            'result_rationale' => $document->result_rationale,
            'commission_protocol_number' => $document->commission_protocol_number,
            'commission_protocol_date' => $document->commission_protocol_date?->format('Y-m-d'),
            'commission_decision' => $document->commission_decision,
            'commission_refusal' => (bool) $document->commission_refusal,
            'conclusion_text' => $document->conclusion_text,
        ];
    }

    private function buildPatientPayload(
        ?Patient $patient,
        ?DriverPsychiatricExamination $document = null,
        bool $isEditing = false
    ): ?array {
        if (!$patient) {
            return null;
        }

        $useSnapshotDiagnosis = $isEditing
            && $document
            && $document->status === DriverPsychiatricExamination::STATUS_PREPARED
            && (int) $document->patient_id === (int) $patient->id;

        $diagnosisCode = trim((string) (
            $useSnapshotDiagnosis ? $document->diagnosis_code : $patient->diagnose?->code
        ));
        $diagnosisTitle = trim((string) (
            $useSnapshotDiagnosis
                ? $document->diagnosis_title
                : ($patient->diagnose?->title ?? $patient->diagnose?->name)
        ));

        $birthDate = $this->patientBirthDate($patient);
        $fullName = collect([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])->filter()->implode(' ');

        return [
            'id' => (int) $patient->id,
            'full_name' => $fullName,
            'birth_at' => $birthDate,
            'birth_at_display' => $this->formatDateDisplay($birthDate),
            'age' => $this->patientAge($patient),
            'phone' => data_get($patient, 'phone')
                ?? data_get($patient, 'telephone')
                ?? data_get($patient, 'mobile_phone'),
            'diagnosis_code' => $diagnosisCode !== '' ? $diagnosisCode : null,
            'diagnosis_title' => $diagnosisTitle !== '' ? $diagnosisTitle : null,
            'diagnosis_label' => ($diagnosisCode !== '' || $diagnosisTitle !== '')
                ? $diagnosisCode . ($diagnosisCode !== '' && $diagnosisTitle !== '' ? ' — ' : '') . $diagnosisTitle
                : 'Диагноз в карте не указан',
        ];
    }

    private function buildPatientSnapshot(Patient $patient): array
    {
        return [
            'full_name' => collect([
                $patient->surname,
                $patient->name,
                $patient->patronym,
            ])->filter()->implode(' '),
            'birth_date' => $this->patientBirthDate($patient),
            'age' => $this->patientAge($patient),
            'sex' => data_get($patient, 'sex') ?? data_get($patient, 'gender'),
            'phone' => data_get($patient, 'phone')
                ?? data_get($patient, 'telephone')
                ?? data_get($patient, 'mobile_phone'),
            'snils' => data_get($patient, 'snils') ?? data_get($patient, 'SNILS'),
        ];
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

    private function authorizeDocument(DriverPsychiatricExamination $document): void
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
            abort(403, 'Для работы с водительскими психиатрическими обследованиями требуется профиль врача и клиника.');
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
