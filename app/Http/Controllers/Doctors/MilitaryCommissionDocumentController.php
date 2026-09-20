<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMilitaryCommissionDocumentRequest;
use App\Models\Doctor;
use App\Models\MilitaryCommissionDocument;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MilitaryCommissionDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $baseQuery = MilitaryCommissionDocument::query()
            ->with(['patient.diagnose', 'creator'])
            ->when(
                !$this->isDebug(),
                static fn (Builder $query) => $query->where('clinic_id', $doctor?->clinic_id)
            );

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)
                ->where('status', MilitaryCommissionDocument::STATUS_DRAFT)
                ->count(),
            'prepared' => (clone $baseQuery)
                ->where('status', MilitaryCommissionDocument::STATUS_PREPARED)
                ->count(),
        ];

        $documents = $baseQuery
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = trim($request->string('q')->toString());
                $like = '%' . $term . '%';

                $query->where(function (Builder $query) use ($like): void {
                    $query
                        ->where('document_number', 'ilike', $like)
                        ->orWhere('requesting_organization', 'ilike', $like)
                        ->orWhere('request_number', 'ilike', $like)
                        ->orWhere('diagnosis_code', 'ilike', $like)
                        ->orWhere('diagnosis_title', 'ilike', $like)
                        ->orWhere('medical_conclusion', 'ilike', $like)
                        ->orWhereHas('patient', function (Builder $patientQuery) use ($like): void {
                            $patientQuery
                                ->where('surname', 'ilike', $like)
                                ->orWhere('name', 'ilike', $like)
                                ->orWhere('patronym', 'ilike', $like);
                        });
                });
            })
            ->when(
                $request->filled('status'),
                static fn (Builder $query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('document_type'),
                static fn (Builder $query) => $query->where('document_type', $request->string('document_type')->toString())
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

        return view('doctors.commissions.military.index', [
            'documents' => $documents,
            'stats' => $stats,
            'statusOptions' => MilitaryCommissionDocument::statusOptions(),
            'documentTypeOptions' => MilitaryCommissionDocument::documentTypeOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);

        $document = new MilitaryCommissionDocument([
            'document_date' => today(),
            'document_type' => MilitaryCommissionDocument::TYPE_MEDICAL_INFORMATION,
            'status' => MilitaryCommissionDocument::STATUS_DRAFT,
        ]);

        $patient = null;

        if ($request->integer('patient_id')) {
            $patient = $this->patientQuery($doctor)->find($request->integer('patient_id'));
        }

        $document->setRelation('patient', $patient);

        return $this->formView($document, false, $doctor);
    }

    public function store(SaveMilitaryCommissionDocumentRequest $request): RedirectResponse
    {
        $doctor = $this->currentDoctor();
        $this->ensureDoctorAvailable($doctor);
        $data = $request->validated();
        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);

        $document = DB::transaction(function () use ($data, $doctor, $patient): MilitaryCommissionDocument {
            $document = MilitaryCommissionDocument::query()->create(
                $this->documentPayload($data, $patient, $doctor)
            );

            $document->update([
                'document_number' => sprintf(
                    'ВВК-%s-%06d',
                    Carbon::parse($document->document_date)->format('Y'),
                    $document->id
                ),
            ]);

            return $document;
        });

        return redirect()
            ->route('doctors.commissions.military.show', $document)
            ->with('status', 'success')
            ->with('message', 'Документ для военкомата создан.');
    }

    public function show(MilitaryCommissionDocument $militaryDocument): View
    {
        $this->authorizeDocument($militaryDocument);
        $militaryDocument->load(['patient.diagnose', 'creator']);

        return view('doctors.commissions.military.show', [
            'document' => $militaryDocument,
        ]);
    }

    public function edit(MilitaryCommissionDocument $militaryDocument): View
    {
        $this->authorizeDocument($militaryDocument);
        $militaryDocument->load(['patient.diagnose', 'creator']);

        return $this->formView(
            $militaryDocument,
            true,
            $this->currentDoctor()
        );
    }

    public function update(
        SaveMilitaryCommissionDocumentRequest $request,
        MilitaryCommissionDocument $militaryDocument
    ): RedirectResponse {
        $this->authorizeDocument($militaryDocument);
        $doctor = $this->currentDoctor();
        $data = $request->validated();
        $patient = $this->findPatientOrFail((int) $data['patient_id'], $doctor);

        $militaryDocument->update(
            $this->documentPayload($data, $patient, $doctor, $militaryDocument)
        );

        return redirect()
            ->route('doctors.commissions.military.show', $militaryDocument)
            ->with('status', 'success')
            ->with('message', 'Изменения сохранены.');
    }

    public function destroy(MilitaryCommissionDocument $militaryDocument): RedirectResponse
    {
        $this->authorizeDocument($militaryDocument);

        if ($militaryDocument->status === MilitaryCommissionDocument::STATUS_PREPARED) {
            return redirect()
                ->route('doctors.commissions.military.index')
                ->with('status', 'error')
                ->with('message', 'Оформленный документ нельзя удалить. Сначала верните его в черновик.');
        }

        $militaryDocument->delete();

        return redirect()
            ->route('doctors.commissions.military.index')
            ->with('status', 'success')
            ->with('message', 'Черновик удалён.');
    }

    public function print(MilitaryCommissionDocument $militaryDocument): View
    {
        $this->authorizeDocument($militaryDocument);
        $militaryDocument->load(['patient.diagnose', 'creator']);

        $documentNumber = trim((string) $militaryDocument->document_number);
        $documentDate = $militaryDocument->document_date?->format('d.m.Y');
        $requestNumber = trim((string) $militaryDocument->request_number);
        $requestDate = $militaryDocument->request_date?->format('d.m.Y');

        $requestBasis = $requestNumber !== ''
            ? 'Запрос № ' . $requestNumber
            : 'Запрос без номера';

        if ($requestDate) {
            $requestBasis .= ' от ' . $requestDate;
        }

        $creatorName = collect([
            $militaryDocument->creator?->surname,
            $militaryDocument->creator?->name,
            $militaryDocument->creator?->patronym,
        ])->filter()->implode(' ');

        $sections = collect([
            'Цель предоставления сведений' => $militaryDocument->purpose,
            'Жалобы' => $militaryDocument->complaints,
            'Анамнез' => $militaryDocument->anamnesis,
            'Объективный статус' => $militaryDocument->objective_status,
            'Результаты обследований' => $militaryDocument->examination_results,
            'Сведения о лечении и динамике' => $militaryDocument->treatment_history,
            'Медицинское заключение' => $militaryDocument->medical_conclusion,
            'Рекомендации' => $militaryDocument->recommendations,
            'Примечания' => $militaryDocument->notes,
        ])->map(
            static fn (mixed $value, string $title): array => [
                'title' => $title,
                'value' => trim((string) $value),
            ]
        )->filter(
            static fn (array $section): bool => $section['value'] !== ''
        )->values()->all();

        $printData = [
            'pageTitle' => $documentNumber !== '' ? $documentNumber : 'Документ ВВК',
            'documentType' => $militaryDocument->documentTypeLabel(),
            'documentHeading' => ($documentNumber !== '' ? $documentNumber : 'Без номера')
                . ($documentDate ? ' от ' . $documentDate : ''),
            'documentDate' => $documentDate ?: '—',
            'footerDocumentNumber' => $documentNumber !== ''
                ? $documentNumber
                : 'Документ без номера',
            'patientFullName' => $militaryDocument->patientFullName(),
            'patientBirthDate' => $militaryDocument->patientBirthDateLabel(),
            'diagnosis' => $militaryDocument->diagnosisLabel(),
            'requestingOrganization' => $militaryDocument->requesting_organization
                ?: 'Не указана',
            'requestBasis' => $requestBasis,
            'creatorName' => $creatorName,
            'sections' => $sections,
        ];

        return view('doctors.commissions.military.print', [
            'printData' => $printData,
        ]);
    }

    private function formView(
        MilitaryCommissionDocument $document,
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

        // При редактировании показываем именно диагноз, зафиксированный в документе,
        // а не возможное новое значение из текущей карты пациента.
        if (
            $initialPatient
            && $isEditing
            && (int) $document->patient_id === (int) $patient?->id
        ) {
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

        return view('doctors.commissions.military.form', [
            'document' => $document,
            'isEditing' => $isEditing,
            'pageTitle' => $isEditing ? 'Редактирование документа ВВК' : 'Новый документ для ВВК',
            'headingTitle' => $isEditing
                ? ($document->document_number ?: 'Редактирование документа')
                : 'Новый документ для военкомата',
            'initialPatient' => $initialPatient,
            'selectedPatientId' => $selectedPatientId,
            'statusOptions' => MilitaryCommissionDocument::statusOptions(),
            'documentTypeOptions' => MilitaryCommissionDocument::documentTypeOptions(),
        ]);
    }

    private function documentPayload(
        array $data,
        Patient $patient,
        ?Doctor $doctor,
        ?MilitaryCommissionDocument $document = null
    ): array {
        $preparedAt = $document?->prepared_at;

        if ($data['status'] === MilitaryCommissionDocument::STATUS_PREPARED && !$preparedAt) {
            $preparedAt = now();
        }

        if ($data['status'] !== MilitaryCommissionDocument::STATUS_PREPARED) {
            $preparedAt = null;
        }

        $patientChanged = !$document || (int) $document->patient_id !== (int) $patient->id;

        $payload = [
            'patient_id' => $patient->id,
            'document_date' => $data['document_date'],
            'document_type' => $data['document_type'],
            'status' => $data['status'],
            'requesting_organization' => $data['requesting_organization'] ?? null,
            'request_number' => $data['request_number'] ?? null,
            'request_date' => $data['request_date'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'complaints' => $data['complaints'] ?? null,
            'anamnesis' => $data['anamnesis'] ?? null,
            'objective_status' => $data['objective_status'] ?? null,
            'examination_results' => $data['examination_results'] ?? null,
            'treatment_history' => $data['treatment_history'] ?? null,
            'medical_conclusion' => $data['medical_conclusion'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'notes' => $data['notes'] ?? null,
            'prepared_at' => $preparedAt,
        ];

        if ($patientChanged) {
            $payload['diagnosis_code'] = $patient->diagnose?->code;
            $payload['diagnosis_title'] = $patient->diagnose?->title ?? $patient->diagnose?->name;
        }

        if (!$document) {
            $payload['created_by_doctor_id'] = $doctor?->id;
            $payload['clinic_id'] = $doctor?->clinic_id;
        }

        return $payload;
    }

    private function buildPatientPayload(?Patient $patient): ?array
    {
        if (!$patient) {
            return null;
        }

        $birthDate = $patient->getRawOriginal('birth_at');
        $birthAtDisplay = null;

        if ($birthDate) {
            try {
                $birthAtDisplay = Carbon::parse($birthDate)->format('d.m.Y');
            } catch (\Throwable) {
                $birthAtDisplay = (string) $patient->birth_at;
            }
        }

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
            'birth_at_display' => $birthAtDisplay,
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

    private function authorizeDocument(MilitaryCommissionDocument $document): void
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
            abort(403, 'Для работы с документами ВВК требуется профиль врача и клиника.');
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
