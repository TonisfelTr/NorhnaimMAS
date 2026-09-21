<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMedicalCommissionRequest;
use App\Models\Doctor;
use App\Models\MedicalCommission;
use App\Models\MedicalCommissionMember;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicalComissionController extends Controller
{
    /**
     * CRUD: список / журнал врачебных комиссий.
     */
    public function index(Request $request): View
    {
        $doctor = $this->currentDoctor();

        $baseQuery = MedicalCommission::query()
            ->with([
                'patient.diagnose',
                'creator',
                'members.doctor',
            ])
            ->when(
                !$this->isDebug() && $doctor?->clinic_id,
                static fn($query) => $query->where('clinic_id', $doctor->clinic_id)
            );

        $statsBase = clone $baseQuery;

        $query = $baseQuery
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim($request->string('q')->toString());
                $like = '%' . $term . '%';

                $query->where(function ($query) use ($like): void {
                    $query
                        ->where('protocol_number', 'ilike', $like)
                        ->orWhere('agenda', 'ilike', $like)
                        ->orWhereHas('patient', function ($patientQuery) use ($like): void {
                            $patientQuery
                                ->where('surname', 'ilike', $like)
                                ->orWhere('name', 'ilike', $like)
                                ->orWhere('patronym', 'ilike', $like);
                        });
                });
            })
            ->when(
                $request->filled('status'),
                static fn($query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('meeting_type'),
                static fn($query) => $query->where('meeting_type', $request->string('meeting_type')->toString())
            )
            ->when(
                $request->filled('date_from'),
                static fn($query) => $query->whereDate('meeting_at', '>=', $request->date('date_from'))
            )
            ->when(
                $request->filled('date_to'),
                static fn($query) => $query->whereDate('meeting_at', '<=', $request->date('date_to'))
            )
            ->orderByDesc('meeting_at')
            ->orderByDesc('id');

        $commissions = $query->paginate(20)->withQueryString();
        $statusOptions = MedicalCommission::statusOptions();

        $commissions->setCollection(
            $commissions->getCollection()->map(
                static function (MedicalCommission $commission) use ($statusOptions): MedicalCommission {
                    $patient = $commission->patient;

                    $patientName = collect([
                        $patient?->surname,
                        $patient?->name,
                        $patient?->patronym,
                    ])->filter()->implode(' ');

                    $patientInitials = collect([
                        $patient?->surname,
                        $patient?->name,
                    ])
                        ->filter()
                        ->map(static fn($part): string => mb_strtoupper(mb_substr((string)$part, 0, 1)))
                        ->implode('');

                    $meetingAt = $commission->meeting_at
                        ? Carbon::parse($commission->meeting_at)
                        : null;

                    $diagnosis = trim(
                        (string)($commission->diagnosis_code ?? '')
                        . ($commission->diagnosis_code && $commission->diagnosis_title ? ' — ' : '')
                        . (string)($commission->diagnosis_title ?? '')
                    );

                    $commission->setAttribute(
                        'ui_patient_name',
                        $patientName !== '' ? $patientName : 'Пациент не указан'
                    );
                    $commission->setAttribute(
                        'ui_patient_initials',
                        $patientInitials !== '' ? $patientInitials : '—'
                    );
                    $commission->setAttribute(
                        'ui_diagnosis',
                        $diagnosis !== '' ? $diagnosis : 'Диагноз не указан'
                    );
                    $commission->setAttribute(
                        'ui_meeting_date',
                        $meetingAt ? $meetingAt->format('d.m.Y') : '—'
                    );
                    $commission->setAttribute(
                        'ui_meeting_time',
                        $meetingAt ? $meetingAt->format('H:i') : ''
                    );
                    $commission->setAttribute(
                        'ui_meeting_type_label',
                        $commission->meeting_type === 'unscheduled' ? 'Внеплановое' : 'Плановое'
                    );
                    $commission->setAttribute(
                        'ui_status_label',
                        $statusOptions[$commission->status] ?? $commission->status
                    );
                    $commission->setAttribute(
                        'ui_agenda_preview',
                        Str::limit(trim((string)($commission->agenda ?? '')) ?: 'Вопрос не указан', 105)
                    );
                    $commission->setAttribute('ui_members_count', $commission->members->count());

                    return $commission;
                }
            )
        );

        $stats = [
            'total' => (clone $statsBase)->count(),
            'draft' => (clone $statsBase)
                ->where('status', MedicalCommission::STATUS_DRAFT)
                ->count(),
            'scheduled' => (clone $statsBase)
                ->where('status', MedicalCommission::STATUS_SCHEDULED)
                ->count(),
            'protocol_ready' => (clone $statsBase)
                ->where('status', MedicalCommission::STATUS_PROTOCOL_READY)
                ->count(),
        ];

        return view('doctors.commissions.index', [
            'commissions' => $commissions,
            'stats' => $stats,
            'statusOptions' => $statusOptions,
            'meetingTypeOptions' => [
                'planned' => 'Плановое',
                'unscheduled' => 'Внеплановое',
            ],
        ]);
    }

    /**
     * CRUD: форма новой ВК.
     */
    public function create(Request $request): View
    {
        $commission = new MedicalCommission([
            'meeting_at' => now()->addHour()->startOfHour(),
            'meeting_type' => MedicalCommission::TYPE_PLANNED,
            'status' => MedicalCommission::STATUS_DRAFT,
            'category' => 'complex_case',
        ]);

        $patient = null;

        if ($request->integer('patient_id')) {
            $patient = Patient::query()
                ->with('diagnose')
                ->find($request->integer('patient_id'));
        }

        $commission->setRelation('patient', $patient);
        $commission->setRelation('members', collect());

        return $this->formView(
            commission: $commission,
            isEditing: false
        );
    }

    /**
     * CRUD: создание.
     */
    public function store(SaveMedicalCommissionRequest $request): RedirectResponse
    {
        $doctor = $this->currentDoctor();
        $data = $request->validated();
        $this->ensureMeetingAtIsNotPast((string)$data['meeting_at']);

        $commission = DB::transaction(function () use ($data, $doctor): MedicalCommission {
            $patient = Patient::query()
                ->with('diagnose')
                ->findOrFail((int)$data['patient_id']);

            $commission = MedicalCommission::query()->create(
                $this->commissionPayload(
                    data: $data,
                    patient: $patient,
                    doctor: $doctor
                )
            );

            $commission->update([
                'protocol_number' => sprintf(
                    'ВК-%s-%06d',
                    Carbon::parse($commission->meeting_at)->format('Y'),
                    $commission->id
                ),
            ]);

            $this->syncMembers(
                commission: $commission,
                members: $data['members'] ?? []
            );

            return $commission;
        });

        return redirect()->route('doctors.commissions.show', $commission)
            ->with('status', 'success')
            ->with('message', 'Врачебная комиссия создана.');
    }

    /**
     * CRUD: просмотр одной ВК.
     */
    public function show(MedicalCommission $commission): View
    {
        $this->authorizeCommission($commission);

        $commission->load([
            'patient.diagnose',
            'members.doctor',
            'creator',
        ]);

        return view('doctors.commissions.show', [
            'commission' => $commission,
            'statusLabel' => MedicalCommission::statusOptions()[$commission->status] ?? $commission->status,
            'categoryLabel' => MedicalCommission::categoryOptions()[$commission->category] ?? $commission->category,
            'meetingTypeLabel' => $commission->meeting_type === 'unscheduled'
                ? 'Внеплановое'
                : 'Плановое',
        ]);
    }

    /**
     * CRUD: редактирование.
     */
    public function edit(MedicalCommission $commission): View
    {
        $this->authorizeCommission($commission);

        $commission->load([
            'patient.diagnose',
            'members.doctor',
            'creator',
        ]);

        return $this->formView(
            commission: $commission,
            isEditing: true
        );
    }

    /**
     * CRUD: обновление.
     */
    public function update(
        SaveMedicalCommissionRequest $request,
        MedicalCommission            $commission
    ): RedirectResponse
    {
        $this->authorizeCommission($commission);
        $data = $request->validated();
        $this->ensureMeetingAtIsNotPast((string)$data['meeting_at']);

        DB::transaction(function () use ($data, $commission): void {
            $patient = Patient::query()
                ->with('diagnose')
                ->findOrFail((int)$data['patient_id']);

            $commission->update(
                $this->commissionPayload(
                    data: $data,
                    patient: $patient,
                    commission: $commission
                )
            );

            $this->syncMembers(
                commission: $commission,
                members: $data['members'] ?? []
            );
        });

        return redirect()->route('doctors.commissions.show', $commission)
            ->with('status', 'success')
            ->with('message', 'Изменения сохранены.');
    }

    /**
     * CRUD: удаление.
     */
    public function destroy(MedicalCommission $commission): RedirectResponse
    {
        $this->authorizeCommission($commission);

        DB::transaction(function () use ($commission): void {
            $commission->members()->delete();
            $commission->delete();
        });

        return redirect()->route('doctors.commissions.index')
            ->with('status', 'success')
            ->with('message', 'Врачебная комиссия удалена.');
    }

    /**
     * Дополнительное действие CRUD-модуля: печатный протокол.
     */
    public function print(MedicalCommission $commission): View
    {
        $this->authorizeCommission($commission);

        $commission->load([
            'patient.diagnose',
            'members.doctor',
            'creator',
        ]);

        $patient = $commission->patient;

        $doctorName = static function (?Doctor $doctor): string {
            return collect([
                $doctor?->surname,
                $doctor?->name,
                $doctor?->patronym,
            ])->filter()->implode(' ');
        };

        $patientFullName = collect([
            $patient?->surname,
            $patient?->name,
            $patient?->patronym,
        ])->filter()->implode(' ');

        $diagnosisCode = trim((string)$commission->diagnosis_code);
        $diagnosisTitle = trim((string)$commission->diagnosis_title);
        $diagnosis = trim(
            $diagnosisCode
            . ($diagnosisCode !== '' && $diagnosisTitle !== '' ? ' — ' : '')
            . $diagnosisTitle
        );

        $members = $commission->members
            ->map(static function ($member) use ($doctorName): array {
                return [
                    'name' => $doctorName($member->doctor) ?: 'Врач не указан',
                    'speciality' => trim((string)$member->doctor?->status),
                    'role' => $member->role_label,
                    'present' => (bool)$member->present,
                    'vote' => $member->present ? $member->vote_label : 'Не присутствовал',
                    'specialOpinion' => trim((string)$member->special_opinion),
                ];
            })
            ->values()
            ->all();

        $presentMembers = $commission->members->where('present', true);
        $chair = $commission->members->firstWhere(
            'role',
            MedicalCommissionMember::ROLE_CHAIR
        );

        $printData = [
            'pageTitle' => $commission->protocol_number ?: 'Протокол врачебной комиссии',
            'protocolNumber' => $commission->protocol_number ?: 'Без номера',
            'meetingAt' => $commission->meeting_at?->format('d.m.Y H:i') ?: '—',
            'meetingType' => $commission->meeting_type_label,
            'status' => $commission->status_label,
            'category' => $commission->category_label,
            'patientFullName' => $patientFullName ?: 'Пациент не указан',
            'patientBirthDate' => $patient?->birth_at
                ? Carbon::parse((string)$patient->birth_at)->format('d.m.Y')
                : '—',
            'diagnosis' => $diagnosis !== '' ? $diagnosis : 'Диагноз не указан',
            'secretaryName' => trim((string)$commission->secretary_name),
            'secretaryPosition' => trim((string)$commission->secretary_position),
            'unscheduledBasis' => trim((string)$commission->unscheduled_basis),
            'agenda' => trim((string)$commission->agenda),
            'decision' => trim((string)$commission->decision),
            'rationale' => trim((string)$commission->rationale),
            'notes' => trim((string)$commission->notes),
            'members' => $members,
            'totalMembers' => $commission->totalMembersCount(),
            'presentMembers' => $commission->presentMembersCount(),
            'quorumRequired' => $commission->quorumRequiredCount(),
            'hasQuorum' => $commission->hasQuorum(),
            'votesFor' => $presentMembers
                ->where('vote', MedicalCommissionMember::VOTE_FOR)
                ->count(),
            'votesAgainst' => $presentMembers
                ->where('vote', MedicalCommissionMember::VOTE_AGAINST)
                ->count(),
            'votesAbstain' => $presentMembers
                ->where('vote', MedicalCommissionMember::VOTE_ABSTAIN)
                ->count(),
            'decisionAdopted' => $commission->isDecisionAdopted(),
            'chairName' => $doctorName($chair?->doctor),
            'creatorName' => $doctorName($commission->creator),
            'protocolReadyAt' => $commission->protocol_ready_at?->format('d.m.Y H:i'),
            'medicalRecordEnteredAt' => $commission->medical_record_entered_at?->format('d.m.Y H:i'),
        ];

        return view('doctors.commissions.print', [
            'printData' => $printData,
        ]);
    }

    private function formView(MedicalCommission $commission, bool $isEditing): View
    {
        $commission->loadMissing([
            'patient.diagnose',
            'members.doctor',
        ]);

        $patient = $commission->patient;
        $initialPatient = $this->buildInitialPatient($patient);
        $initialMembers = $this->buildInitialMembers($commission);
        $oldInput = session()->getOldInput();

        $formValue = static function (string $key, mixed $default = null) use ($oldInput): mixed {
            return array_key_exists($key, $oldInput) ? $oldInput[$key] : $default;
        };

        $meetingAt = $formValue(
            'meeting_at',
            $commission->meeting_at
                ? Carbon::parse($commission->meeting_at)->format('Y-m-d\TH:i')
                : null
        );
        $meetingAtMinimum = now()->startOfMinute()->format('Y-m-d\TH:i');

        $meetingType = (string)$formValue(
            'meeting_type',
            $commission->meeting_type ?? MedicalCommission::TYPE_PLANNED
        );

        $status = (string)$formValue(
            'status',
            $commission->status ?? MedicalCommission::STATUS_DRAFT
        );

        $category = (string)$formValue(
            'category',
            $commission->category ?? 'complex_case'
        );

        $patientId = $formValue('patient_id', $commission->patient_id);
        $medicalRecordEntered = (bool)$formValue(
            'medical_record_entered',
            (bool)$commission->medical_record_entered_at
        );

        $protocolNumber = trim((string)($commission->protocol_number ?? ''));
        $pageTitle = $isEditing ? 'Редактирование ВК' : 'Новая врачебная комиссия';
        $headingTitle = $isEditing
            ? ($protocolNumber !== '' ? $protocolNumber : 'Редактирование ВК')
            : 'Новая врачебная комиссия';

        $statusOptions = $this->buildSelectOptions(
            MedicalCommission::statusOptions(),
            $status
        );

        $categoryOptions = $this->buildSelectOptions(
            MedicalCommission::categoryOptions(),
            $category
        );

        $meetingTypeOptions = [
            [
                'value' => 'planned',
                'label' => 'Плановое',
                'selected' => $meetingType === 'planned',
            ],
            [
                'value' => 'unscheduled',
                'label' => 'Внеплановое',
                'selected' => $meetingType === 'unscheduled',
            ],
        ];

        $patientSearchValue = (string)($initialPatient['full_name'] ?? '');
        $patientDisplayName = $patientSearchValue !== '' ? $patientSearchValue : 'Пациент не выбран';
        $patientBirthText = !empty($initialPatient['birth_at_display'])
            ? 'Дата рождения: ' . $initialPatient['birth_at_display']
            : 'Выберите пациента из поиска';

        $diagnosisCode = trim((string)($initialPatient['diagnosis']['code'] ?? ''));
        $diagnosisTitle = trim((string)($initialPatient['diagnosis']['title'] ?? ''));
        $patientDiagnosisText = $diagnosisCode !== '' || $diagnosisTitle !== ''
            ? 'Диагноз: '
            . $diagnosisCode
            . ($diagnosisCode !== '' && $diagnosisTitle !== '' ? ' — ' : '')
            . $diagnosisTitle
            : 'Диагноз: не установлен';

        $successMessage = session('status') === 'success'
            ? (string)session('message', '')
            : '';

        $validationErrors = session('errors')
            ? session('errors')->all()
            : [];

        return view('doctors.commissions.form', [
            'commission' => $commission,
            'isEditing' => $isEditing,
            'pageTitle' => $pageTitle,
            'headingTitle' => $headingTitle,
            'patientId' => $patientId,
            'meetingAt' => $meetingAt,
            'meetingAtMinimum' => $meetingAtMinimum,
            'meetingTypeOptions' => $meetingTypeOptions,
            'statusOptions' => $statusOptions,
            'categoryOptions' => $categoryOptions,
            'unscheduledBasisClass' => $meetingType === 'unscheduled' ? '' : 'd-none',
            'unscheduledBasis' => (string)$formValue(
                'unscheduled_basis',
                $commission->unscheduled_basis
            ),
            'agenda' => (string)$formValue('agenda', $commission->agenda),
            'decision' => (string)$formValue('decision', $commission->decision),
            'rationale' => (string)$formValue('rationale', $commission->rationale),
            'secretaryName' => (string)$formValue('secretary_name', $commission->secretary_name),
            'secretaryPosition' => (string)$formValue('secretary_position', $commission->secretary_position),
            'notes' => (string)$formValue('notes', $commission->notes),
            'medicalRecordEntered' => $medicalRecordEntered,
            'initialPatient' => $initialPatient,
            'initialMembers' => $initialMembers,
            'patientSearchValue' => $patientSearchValue,
            'patientClearClass' => $initialPatient ? '' : 'd-none',
            'patientCardClass' => $initialPatient ? '' : 'is-empty',
            'patientDisplayName' => $patientDisplayName,
            'patientBirthText' => $patientBirthText,
            'patientDiagnosisText' => $patientDiagnosisText,
            'successMessage' => $successMessage,
            'hasValidationErrors' => $validationErrors !== [],
            'validationErrors' => $validationErrors,
        ]);
    }

    private function ensureMeetingAtIsNotPast(string $meetingAt): void
    {
        if (Carbon::parse($meetingAt)->lt(now()->startOfMinute())) {
            throw ValidationException::withMessages([
                'meeting_at' => 'Дата и время заседания не могут быть раньше текущего времени.',
            ]);
        }
    }

    private function commissionPayload(
        array              $data,
        Patient            $patient,
        ?Doctor            $doctor = null,
        ?MedicalCommission $commission = null
    ): array
    {
        $protocolReadyAt = $commission ? $commission->protocol_ready_at : null;

        if ($data['status'] === MedicalCommission::STATUS_PROTOCOL_READY && !$protocolReadyAt) {
            $protocolReadyAt = now();
        }

        if ($data['status'] !== MedicalCommission::STATUS_PROTOCOL_READY) {
            $protocolReadyAt = null;
        }

        $medicalRecordAt = $commission ? $commission->medical_record_entered_at : null;

        if (!empty($data['medical_record_entered']) && !$medicalRecordAt) {
            $medicalRecordAt = now();
        }

        if (empty($data['medical_record_entered'])) {
            $medicalRecordAt = null;
        }

        $payload = [
            'patient_id' => $patient->id,
            'meeting_at' => $data['meeting_at'],
            'meeting_type' => $data['meeting_type'],
            'status' => $data['status'],
            'category' => $data['category'],
            'diagnosis_code' => $patient->diagnose?->code,
            'diagnosis_title' => $patient->diagnose?->title ?? $patient->diagnose?->name,
            'secretary_name' => $data['secretary_name'] ?? null,
            'secretary_position' => $data['secretary_position'] ?? null,
            'unscheduled_basis' => $data['unscheduled_basis'] ?? null,
            'agenda' => $data['agenda'],
            'decision' => $data['decision'] ?? null,
            'rationale' => $data['rationale'] ?? null,
            'notes' => $data['notes'] ?? null,
            'protocol_ready_at' => $protocolReadyAt,
            'medical_record_entered_at' => $medicalRecordAt,
        ];

        if (!$commission) {
            $payload['created_by_doctor_id'] = $doctor?->id;
            $payload['clinic_id'] = $doctor?->clinic_id;
        }

        return $payload;
    }

    private function buildInitialPatient(?Patient $patient): ?array
    {
        if (!$patient) {
            return null;
        }

        $fullName = collect([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])->filter()->implode(' ');

        $birthAt = null;

        if ($patient->birth_at) {
            try {
                $birthAt = Carbon::parse($patient->birth_at);
            } catch (\Throwable) {
                $birthAt = null;
            }
        }

        return [
            'id' => (int)$patient->id,
            'full_name' => $fullName,
            'birth_at' => $birthAt?->format('Y-m-d'),
            'birth_at_display' => $birthAt?->format('d.m.Y'),
            'diagnosis' => [
                'id' => $patient->diagnose_id,
                'code' => $patient->diagnose?->code,
                'title' => $patient->diagnose?->title ?? $patient->diagnose?->name,
            ],
        ];
    }

    private function buildInitialMembers(MedicalCommission $commission): array
    {
        return $commission->members
            ->map(static function ($member): array {
                $doctor = $member->doctor;

                $name = collect([
                    $doctor?->surname,
                    $doctor?->name,
                    $doctor?->patronym,
                ])->filter()->implode(' ');

                return [
                    'doctor_id' => (int)$member->doctor_id,
                    'name' => $name,
                    'speciality' => $doctor?->status,
                    'role' => $member->role,
                    'present' => (bool)$member->present,
                    'vote' => $member->vote,
                    'special_opinion' => $member->special_opinion,
                ];
            })
            ->values()
            ->all();
    }

    private function buildSelectOptions(array $options, string $selected): array
    {
        return collect($options)
            ->map(static function (string $label, string $value) use ($selected): array {
                return [
                    'value' => $value,
                    'label' => $label,
                    'selected' => $value === $selected,
                ];
            })
            ->values()
            ->all();
    }

    private function syncMembers(MedicalCommission $commission, array $members): void
    {
        $commission->members()->delete();

        foreach ($members as $member) {
            $present = (bool)($member['present'] ?? false);

            $commission->members()->create([
                'doctor_id' => (int)$member['doctor_id'],
                'role' => $member['role'],
                'present' => $present,
                'vote' => $present ? ($member['vote'] ?? null) : null,
                'special_opinion' => $member['special_opinion'] ?? null,
            ]);
        }
    }

    private function authorizeCommission(MedicalCommission $commission): void
    {
        if ($this->isDebug()) {
            return;
        }

        $doctor = $this->currentDoctor();

        if (
            $doctor?->clinic_id
            && $commission->clinic_id
            && (int)$doctor->clinic_id !== (int)$commission->clinic_id
        ) {
            abort(403);
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
            : (bool)config('app.debug');
    }
}
