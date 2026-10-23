<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\HospitalBedAssignment;
use App\Models\HospitalDepartment;
use App\Models\HospitalDepartmentTransfer;
use App\Support\HospitalPatientAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class IndexController extends Controller
{
    public function index(Request $request, HospitalPatientAccess $access): View
    {
        $input = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['overview', 'patients'])],
            'mode' => ['sometimes', 'string', Rule::in(['inpatient', 'outpatient'])],
            'scope' => ['sometimes', 'string', Rule::in(['mine', 'department'])],
            'department_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        abort_unless($user, 403);
        $isAdmin = $access->isDebugAdmin($user);
        $doctor = null;
        $clinicId = null;

        if ($isAdmin) {
            abort_unless($access->isAdminReader($user), 403);
            $clinicIds = $access->adminClinicIds($user);
            // The bootstrap debug admin can see all inpatient facilities.
            $isInpatient = true;
            $departments = HospitalDepartment::query()
                ->whereIn('clinic_id', $clinicIds)
                ->where('enabled', true)
                ->with(['doctors', 'headDoctor', 'clinic'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'number', 'clinic_id', 'head_doctor_id']);
        } else {
            abort_unless($user->hasRole('doctors'), 403);
            $doctor = $user->doctor()->with('clinic')->first();
            abort_unless($doctor instanceof Doctor && $doctor->clinic !== null, 403);
            $clinicId = (int) $doctor->clinic_id;
            $clinicIds = [$clinicId];
            $isInpatient = $doctor->clinic->clinic_type === Clinic::TYPE_INPATIENT;
            $departments = HospitalDepartment::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->where(function (Builder $query) use ($doctor): void {
                    $query->where('head_doctor_id', $doctor->id)
                        ->orWhereHas('doctors', function (Builder $assignedDoctor) use ($doctor): void {
                            $assignedDoctor->where('doctors.id', $doctor->id)
                                ->where('doctors.clinic_id', $doctor->clinic_id);
                        });
                })
                ->when(!$isInpatient, fn (Builder $query): Builder => $query->whereRaw('1 = 0'))
                ->with(['doctors', 'headDoctor', 'clinic'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'number', 'clinic_id', 'head_doctor_id']);
        }

        // Для врача режим всегда задаёт clinic_type его клиники.
        // Только специальный debug-admin может переключать рабочий стол,
        // поскольку у него нет собственной клиники.
        $workbenchMode = $isAdmin
            ? ($input['mode'] ?? 'inpatient')
            : ($isInpatient ? 'inpatient' : 'outpatient');

        // Администратор-отладчик не привязан к одной клинике: выбирает клинику
        // для каждого действия в модальном окне; это не назначение врача.
        // «Стационар» — Clinic с clinic_type=inpatient. Показываем также
        // его головную организацию, но не расширяем доступ к пациентам.
        $workbenchClinicName = $doctor?->clinic?->name;
        $workbenchParentName = null;
        $parentClinicNames = Clinic::query()->whereNull('deleted_at')->pluck('name', 'id');
        if ($doctor?->clinic?->parent_clinic_id) {
            $workbenchParentName = $parentClinicNames[$doctor->clinic->parent_clinic_id] ?? null;
        }
        $quickActionInpatientClinics = $isAdmin
            ? Clinic::query()->where('clinic_type', Clinic::TYPE_INPATIENT)
                ->orderBy('name')->get(['id', 'name', 'parent_clinic_id'])
                ->each(static function (Clinic $hospital) use ($parentClinicNames): void {
                    $hospital->setAttribute('parent_name', $hospital->parent_clinic_id
                        ? ($parentClinicNames[$hospital->parent_clinic_id] ?? null)
                        : null);
                })
            : collect();
        $quickActionOutpatientClinics = $isAdmin
            ? Clinic::query()->where('clinic_type', 'outpatient')
                ->orderBy('name')->get(['id', 'name'])
            : collect();

        $workbenchInstitutionTitle = $workbenchClinicName;
        $workbenchInstitutionParent = $workbenchParentName;
        if ($isAdmin) {
            if ($workbenchMode === 'inpatient' && $quickActionInpatientClinics->count() === 1) {
                $hospital = $quickActionInpatientClinics->first();
                $workbenchInstitutionTitle = $hospital?->name;
                $workbenchInstitutionParent = $hospital?->parent_name;
            } else {
                $workbenchInstitutionTitle = $workbenchMode === 'inpatient'
                    ? 'Все стационары системы' : 'Амбулаторные учреждения системы';
                $workbenchInstitutionParent = null;
            }
        }

        // Амбулаторный рабочий стол не делает запросов к госпитализациям.
        // Его собственные показатели будут реализованы отдельно.
        if ($workbenchMode === 'outpatient') {
            return view('doctors.index', [
                'isAdmin' => $isAdmin,
                'isInpatient' => $isInpatient,
                'workbenchMode' => $workbenchMode,
                'tab' => 'overview',
                'quickActionInpatientClinics' => $quickActionInpatientClinics,
                'quickActionOutpatientClinics' => $quickActionOutpatientClinics,
                'workbenchClinicName' => $workbenchClinicName,
                'workbenchParentName' => $workbenchParentName,
                'workbenchInstitutionTitle' => $workbenchInstitutionTitle,
                'workbenchInstitutionParent' => $workbenchInstitutionParent,
            ]);
        }

        $tab = $workbenchMode === 'outpatient' ? 'overview' : ($input['tab'] ?? 'overview');
        $scope = $isAdmin ? 'department' : ($input['scope'] ?? 'mine');
        $search = trim($input['q'] ?? '');
        $selectedDepartmentId = (int) ($input['department_id'] ?? 0);
        if ($tab === 'overview') {
            $scope = $isAdmin ? 'department' : 'mine';
            $search = '';
            $selectedDepartmentId = 0;
        }

        $allowedDepartmentIds = $departments->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        if ($selectedDepartmentId > 0 && !in_array($selectedDepartmentId, $allowedDepartmentIds, true)) {
            abort(403, 'Нет доступа к выбранному отделению.');
        }
        $visibleDepartmentIds = $selectedDepartmentId > 0
            ? [$selectedDepartmentId]
            : $allowedDepartmentIds;

        // Department heads may assign attending doctors; the bootstrap debug
        // admin may do so on the test instance only.
        $headedDepartmentIds = $isAdmin
            ? $allowedDepartmentIds
            : $departments->filter(static fn (HospitalDepartment $department): bool =>
                (int) $department->head_doctor_id === (int) $doctor->id
            )->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $doctorOptionsByDepartment = [];
        foreach ($departments as $department) {
            $departmentId = (int) $department->id;
            if (!in_array($departmentId, $headedDepartmentIds, true)) {
                continue;
            }

            $availableDoctors = $department->doctors
                ->filter(static fn (Doctor $candidate): bool =>
                    (int) $candidate->clinic_id === (int) $department->clinic_id
                );

            if ($department->headDoctor
                && (int) $department->headDoctor->clinic_id === (int) $department->clinic_id) {
                $availableDoctors = $availableDoctors->push($department->headDoctor);
            }
            // Врачи не исключаются из списка после назначения пациенту:
            // один лечащий врач может вести несколько госпитализаций одновременно.
            $options = $availableDoctors
                ->unique('id')->sortBy('surname')
                ->map(fn (Doctor $candidate): array => [
                    'id' => (int) $candidate->id,
                    'name' => $this->formatDoctorName($candidate),
                ])->values();

            // Если в отделении два врача с одинаковым ФИО, добавляем ID
            // только к совпадающим именам, чтобы выбор был однозначным.
            $nameCounts = $options->countBy(fn (array $option): string =>
                $this->normalizeDoctorName($option['name'])
            );
            $doctorOptionsByDepartment[$departmentId] = $options
                ->map(fn (array $option): array => $option + [
                    'search_label' => ($nameCounts[$this->normalizeDoctorName($option['name'])] ?? 0) > 1
                        ? $option['name'] . ' (#' . $option['id'] . ')'
                        : $option['name'],
                ])->all();
        }

        $baseQuery = $access->activeAssignments($clinicIds, $visibleDepartmentIds);
        if (!$isAdmin) {
            $patientColumns = $this->patientClinicColumns();
            $baseQuery->whereHas('patient', function (Builder $patient) use ($clinicId, $patientColumns): void {
                $this->restrictPatientToClinic($patient, $clinicId, $patientColumns);
            });
        }

        $departmentCount = (clone $baseQuery)->count();
        $myCount = $isAdmin ? 0 : (clone $baseQuery)
            ->where('attending_doctor_id', $doctor->id)->count();
        $unassignedCount = (clone $baseQuery)->whereNull('attending_doctor_id')->count();
        $admittedTodayCount = (clone $baseQuery)->whereDate('assigned_at', Carbon::today())->count();

        $query = (clone $baseQuery)
            ->when(!$isAdmin && $scope === 'mine', fn (Builder $q): Builder =>
                $q->where('attending_doctor_id', $doctor->id)
            )
            ->when($search !== '', function (Builder $query) use ($search): void {
                $pattern = '%' . $search . '%';
                $query->whereHas('patient', function (Builder $patient) use ($pattern): void {
                    $patient->where(function (Builder $names) use ($pattern): void {
                        $names->where('surname', 'ilike', $pattern)
                            ->orWhere('name', 'ilike', $pattern)
                            ->orWhere('patronym', 'ilike', $pattern)
                            ->orWhereRaw("CONCAT_WS(' ', surname, name, patronym) ILIKE ?", [$pattern]);
                    });
                });
            })
            ->with(['patient', 'department', 'bed.room.department', 'attendingDoctor', 'latestDepartmentTransfer'])
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');

        $assignments = $query->paginate($tab === 'overview' ? 6 : 15)->withQueryString();
        $patients = $assignments->getCollection()->map(function (HospitalBedAssignment $assignment) use (
            $headedDepartmentIds, $doctorOptionsByDepartment
        ): array {
            $patient = $assignment->patient;
            $room = $assignment->bed?->room;
            $departmentId = (int) $assignment->department_id;
            $birth = null;
            try {
                $rawBirth = $patient?->getRawOriginal('birth_at');
                $birth = $rawBirth ? Carbon::parse($rawBirth) : null;
            } catch (Throwable) {
                // A malformed legacy birth date cannot take the workbench offline.
            }

            $assignedDoctorName = $assignment->attendingDoctor
                ? $this->formatDoctorName($assignment->attendingDoctor)
                : null;
            $lastTransfer = $assignment->latestDepartmentTransfer;
            $departmentOptions = $doctorOptionsByDepartment[$departmentId] ?? [];

            return [
                'assignment_id' => (int) $assignment->id,
                'full_name' => trim(implode(' ', array_filter([
                    $patient?->surname, $patient?->name, $patient?->patronym,
                ]))),
                'birth_date' => $birth?->format('d.m.Y') ?? '—',
                'age' => $birth?->age,
                'department' => $assignment->department?->name ?? '—',
                'room' => $room?->number ?? '—',
                'bed' => $assignment->bed?->number ?? '—',
                'without_bed' => $assignment->bed_id === null,
                'admitted_at' => $assignment->assigned_at?->format('d.m.Y H:i') ?? '—',
                'doctor_id' => $assignment->attending_doctor_id ? (int) $assignment->attending_doctor_id : null,
                'doctor_name' => $assignedDoctorName ?? 'Не назначен',
                'can_assign' => in_array($departmentId, $headedDepartmentIds, true),
                'doctor_options' => $departmentOptions,
                'last_transfer' => $lastTransfer ? [
                    'from' => $lastTransfer->from_department_name,
                    'to' => $lastTransfer->to_department_name,
                    'by' => $lastTransfer->transferred_by_name,
                    'at' => $lastTransfer->transferred_at?->format('d.m.Y H:i:s') ?? '—',
                ] : null,
                'medical_card_url' => route('doctors.patients.medical_card', ['patient' => $assignment->patient_id]),
            ];
        });

        // IMPORTANT: these admissions must remain visible even with no bed,
        // and they are not silently placed when a bed becomes free.
        $corridorPatients = collect();
        $corridorCount = (clone $baseQuery)->whereNull('bed_id')->count();
        if ($tab === 'overview' && $isInpatient && $visibleDepartmentIds !== []) {
            $corridorPatients = (clone $baseQuery)->whereNull('bed_id')
                ->with(['patient', 'department', 'latestDepartmentTransfer'])
                ->orderByDesc('assigned_at')->limit(15)->get()
                ->map(function (HospitalBedAssignment $assignment) use ($isAdmin): array {
                    $freeBeds = \App\Models\HospitalBed::query()
                        ->where('clinic_id', (int) $assignment->department->clinic_id)
                        ->where('enabled', true)
                        ->whereHas('room', fn (Builder $room): Builder => $room
                            ->where('clinic_id', $assignment->department->clinic_id)
                            ->where('department_id', $assignment->department_id)
                            ->where('enabled', true))
                        ->whereDoesntHave('currentAssignment')->count();
                    return [
                        'patient_name' => trim(implode(' ', array_filter([
                            $assignment->patient?->surname, $assignment->patient?->name,
                            $assignment->patient?->patronym,
                        ]))),
                        'department' => (string) ($assignment->department?->name ?? '—'),
                        'free_beds' => $freeBeds,
                        'medical_card_url' => route('doctors.patients.medical_card', ['patient' => $assignment->patient_id]),
                        'rooms_url' => !$isAdmin ? route('doctors.inpatient.rooms.index') : null,
                        'since' => $assignment->latestDepartmentTransfer?->transferred_at?->format('d.m.Y H:i')
                            ?? $assignment->assigned_at?->format('d.m.Y H:i') ?? '—',
                    ];
                })->values();
        }

        // Pending reminders stay visible until a doctor is assigned (or discharge).
        // They are scoped to EXACTLY the current user's permitted active admissions,
        // including receiving-department patients who are not yet "mine".
        $transferReminders = collect();
        if ($tab === 'overview' && $isInpatient && $visibleDepartmentIds !== []) {
            $transferReminders = (clone $baseQuery)
                ->whereNull('attending_doctor_id')
                ->whereHas('departmentTransfers')
                ->with(['patient', 'bed.room.department', 'latestDepartmentTransfer'])
                ->withMax('departmentTransfers', 'transferred_at')
                ->orderByDesc('department_transfers_max_transferred_at')
                ->limit(5)
                ->get()
                ->map(function (HospitalBedAssignment $assignment): ?array {
                    $transfer = $assignment->latestDepartmentTransfer;
                    $currentDepartmentId = (int) $assignment->department_id;
                    if (!$transfer || (int) $transfer->to_department_id !== $currentDepartmentId) {
                        return null;
                    }
                    return [
                        'patient_name' => trim(implode(' ', array_filter([
                            $assignment->patient?->surname, $assignment->patient?->name,
                            $assignment->patient?->patronym,
                        ]))),
                        'medical_card_url' => route('doctors.patients.medical_card', ['patient' => $assignment->patient_id]),
                        'from' => $transfer->from_department_name,
                        'to' => $transfer->to_department_name,
                        'by' => $transfer->transferred_by_name,
                        'at' => $transfer->transferred_at?->format('d.m.Y H:i:s') ?? '—',
                    ];
                })
                ->filter()
                ->values();
        }

        // Source- and destination-department staff can see the last 30 days
        // of transfer notices. This is NOT authorization to open old medcards.
        // The full history is kept indefinitely in hospital_department_transfers.
        $transferNotifications = collect();
        if ($tab === 'overview' && $isInpatient && $allowedDepartmentIds !== []) {
            $transferNotifications = HospitalDepartmentTransfer::query()
                ->whereIn('clinic_id', $clinicIds)
                ->where(function (Builder $events) use ($allowedDepartmentIds): void {
                    $events->whereIn('from_department_id', $allowedDepartmentIds)
                        ->orWhereIn('to_department_id', $allowedDepartmentIds);
                })
                ->where('transferred_at', '>=', now()->subDays(30))
                ->with('patient')
                ->orderByDesc('transferred_at')
                ->orderByDesc('id')
                ->limit(8)
                ->get()
                ->map(static function (HospitalDepartmentTransfer $event): array {
                    $name = trim(implode(' ', array_filter([
                        $event->patient?->surname, $event->patient?->name,
                        $event->patient?->patronym,
                    ])));
                    return [
                        'patient_name' => $name !== '' ? $name : 'Пациент #' . $event->patient_id,
                        'from' => $event->from_department_name,
                        'to' => $event->to_department_name,
                        'by' => $event->transferred_by_name,
                        'at' => $event->transferred_at?->format('d.m.Y H:i:s') ?? '—',
                    ];
                });
        }

        return view('doctors.index', compact(
            'isAdmin', 'isInpatient', 'tab', 'scope', 'search', 'departments',
            'selectedDepartmentId', 'departmentCount', 'myCount', 'unassignedCount',
            'admittedTodayCount', 'assignments', 'patients', 'transferReminders',
            'transferNotifications', 'corridorPatients', 'corridorCount',
            'quickActionInpatientClinics', 'quickActionOutpatientClinics', 'workbenchMode',
            'workbenchClinicName', 'workbenchParentName',
            'workbenchInstitutionTitle', 'workbenchInstitutionParent'
        ));
    }

    public function updateAttendingDoctor(
        Request $request,
        HospitalBedAssignment $assignment,
        HospitalPatientAccess $access
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user, 403);
        $isAdmin = $access->isDebugAdmin($user);
        $doctor = null;

        if ($isAdmin) {
            $clinicIds = $access->adminClinicIds($user);
        } else {
            abort_unless($user->hasRole('doctors'), 403);
            $doctor = $user->doctor()->with('clinic')->first();
            abort_unless($doctor instanceof Doctor && $doctor->clinic !== null
                && $doctor->clinic->clinic_type === Clinic::TYPE_INPATIENT, 403);
            $clinicIds = [(int) $doctor->clinic_id];
        }

        // Селектор отправляет ID врача, а не введённое пользователем ФИО.
        $input = $request->validate([
            'attending_doctor_id' => ['present', 'nullable', 'integer', 'exists:doctors,id'],
        ]);
        $targetDoctorId = filled($input['attending_doctor_id'] ?? null)
            ? (int) $input['attending_doctor_id']
            : null;

        $change = DB::transaction(function () use (
            $assignment, $doctor, $isAdmin, $clinicIds, $targetDoctorId
        ): array {
            $current = HospitalBedAssignment::query()
                ->whereKey($assignment->getKey())->whereNull('released_at')
                ->lockForUpdate()->firstOrFail();

            $bed = $current->bed()->with('room.department')->first();
            $department = $current->department()->first();
            abort_unless($department && $department->enabled
                && in_array((int) $department->clinic_id, $clinicIds, true)
                && ($bed === null || (
                    (int) $bed->clinic_id === (int) $department->clinic_id
                    && (int) ($bed->room?->department_id ?? 0) === (int) $department->id
                )), 403);

            if (!$isAdmin) {
                abort_unless((int) $department->head_doctor_id === (int) $doctor->id, 403);
                $patient = \App\Models\Patient::query()->whereKey($current->patient_id);
                $this->restrictPatientToClinic($patient, (int) $doctor->clinic_id, $this->patientClinicColumns());
                abort_unless($patient->exists(), 403);
            }

            // Не ограничиваем число пациентов, закреплённых за одним врачом.
            // Но каждый выбор проверяем в рамках клиники и отделения госпитализации.
            if ($targetDoctorId !== null) {
                $target = Doctor::query()
                    ->whereKey($targetDoctorId)
                    ->where('clinic_id', $department->clinic_id)
                    ->first();
                $inDepartment = $department->doctors()
                    ->where('doctors.id', $targetDoctorId)
                    ->where('doctors.clinic_id', $department->clinic_id)
                    ->exists();
                if (!$target || (
                    $targetDoctorId !== (int) $department->head_doctor_id
                    && !$inDepartment
                )) {
                    throw ValidationException::withMessages([
                        'attending_doctor_id' => 'Выбранный врач не относится к данному отделению.',
                    ]);
                }
            }

            $previousDoctorId = $current->attending_doctor_id;
            $current->attending_doctor_id = $targetDoctorId;
            $current->save();

            return [
                'department_id' => (int) $department->id,
                'assignment_id' => (int) $current->id,
                'previous_doctor_id' => $previousDoctorId,
                'attending_doctor_id' => $targetDoctorId,
            ];
        }, 3);

        Log::info('Hospital attending doctor changed', [
            'actor_user_id' => $user->id,
            'assignment_id' => $change['assignment_id'],
            'previous_doctor_id' => $change['previous_doctor_id'],
            'attending_doctor_id' => $change['attending_doctor_id'],
        ]);

        return redirect()->route('doctors.main', [
            'tab' => 'patients',
            'scope' => 'department',
            'department_id' => $change['department_id'],
        ])->with('success', 'Лечащий врач обновлён.');
    }

    private function formatDoctorName(Doctor $doctor): string
    {
        return trim(implode(' ', array_filter([
            $doctor->surname, $doctor->name, $doctor->patronym,
        ])));
    }

    private function normalizeDoctorName(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
    }

    /** @return array{clinic_id:bool,doctor_id:bool,registry:bool} */
    private function patientClinicColumns(): array
    {
        return [
            'clinic_id' => Schema::hasColumn('patients', 'clinic_id'),
            'doctor_id' => Schema::hasColumn('patients', 'doctor_id'),
            'registry' => Schema::hasTable('registry_records')
                && Schema::hasColumn('registry_records', 'clinic_id')
                && Schema::hasColumn('registry_records', 'patient_id'),
        ];
    }

    /**
     * Совпадает по смыслу с существующей проверкой принадлежности пациента клинике
     * в InpatientAdmissionController. Без признаков связи доступ запрещён.
     *
     * @param array{clinic_id:bool,doctor_id:bool,registry:bool} $columns
     */
    private function restrictPatientToClinic(Builder $patient, int $clinicId, array $columns): void
    {
        if (!$columns['clinic_id'] && !$columns['doctor_id'] && !$columns['registry']) {
            $patient->whereRaw('1 = 0');
            return;
        }

        $patient->where(function (Builder $scope) use ($clinicId, $columns): void {
            if ($columns['clinic_id']) {
                $scope->where('patients.clinic_id', $clinicId);
            }
            if ($columns['doctor_id']) {
                $scope->orWhereIn('patients.doctor_id', DB::table('doctors')
                    ->select('id')->where('clinic_id', $clinicId));
            }
            if ($columns['registry']) {
                $scope->orWhereIn('patients.id', DB::table('registry_records')
                    ->select('patient_id')->where('clinic_id', $clinicId));
            }
        });
    }
}
