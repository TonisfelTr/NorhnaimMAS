<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedicationAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicationAssignmentsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        /** @var Patient|null $patient */
        $patient = $user->patient()->first();

        $allAssignments = $patient
            ? PatientMedicationAssignment::query()
                ->with('clinic')
                ->where('patient_id', (int) $patient->id)
                ->orderByRaw(
                    "CASE status "
                    . "WHEN 'active' THEN 0 "
                    . "WHEN 'completed' THEN 1 "
                    . "ELSE 2 END"
                )
                ->orderByDesc('starts_on')
                ->orderByDesc('id')
                ->get()
            : collect();

        // Клиники доступны в назначениях только после подтверждённого
        // состоявшегося приёма пациента у психиатра в этой клинике.
        $psychiatryClinicIds = $patient
            ? $this->psychiatryVisitedClinicIds($patient)
            : [];

        $priorityClinicMemberships = $patient
            ? $patient->clinicMemberships()
                ->where('is_active', true)
                ->whereIn('clinic_id', $psychiatryClinicIds)
                ->with('clinic')
                ->get()
                ->filter(static fn ($membership): bool => $membership->clinic !== null)
                ->sortBy(static fn ($membership): string =>
                    sprintf(
                        '%d-%s',
                        $membership->is_primary ? 0 : 1,
                        mb_strtolower((string) $membership->clinic->name)
                    )
                )
                ->values()
            : collect();

        // Единый набор доступных клиник: пациент действительно был у психиатра
        // и клиника остаётся активной в его медицинской карте.
        $availableClinicIds = $priorityClinicMemberships
            ->pluck('clinic_id')
            ->map(static fn ($clinicId): int => (int) $clinicId)
            ->filter(static fn (int $clinicId): bool => $clinicId > 0)
            ->unique()
            ->values()
            ->all();

        $availableClinicLookup = array_fill_keys($availableClinicIds, true);

        // Не смешиваем назначения из других клиник и legacy-записи без clinic_id.
        $allAssignments = $allAssignments
            ->filter(static function (PatientMedicationAssignment $assignment) use ($availableClinicLookup): bool {
                $clinicId = (int) ($assignment->clinic_id ?? 0);

                return $clinicId > 0
                    && isset($availableClinicLookup[$clinicId]);
            })
            ->values();

        $primaryMembership = $priorityClinicMemberships->firstWhere(
            'is_primary',
            true
        );

        if (!$primaryMembership && $patient && $patient->clinic_id) {
            $primaryMembership = $priorityClinicMemberships->firstWhere(
                'clinic_id',
                (int) $patient->clinic_id
            );
        }

        if (!$primaryMembership) {
            $primaryMembership = $priorityClinicMemberships->first();
        }

        $priorityClinicId = $primaryMembership
            ? (int) $primaryMembership->clinic_id
            : null;

        // Вкладки строятся только по клиникам, в которых подтверждён
        // состоявшийся приём именно у психиатра.
        $clinicTabMap = [];

        foreach ($priorityClinicMemberships as $membership) {
            $clinicId = (int) $membership->clinic_id;

            if ($clinicId <= 0) {
                continue;
            }

            $key = 'clinic:' . $clinicId;
            $clinicTabMap[$key] = [
                'key' => $key,
                'clinic_id' => $clinicId,
                'name' => trim((string) $membership->clinic->name)
                    ?: 'Клиника #' . $clinicId,
                'total' => 0,
                'active_count' => 0,
                'history_count' => 0,
            ];
        }

        foreach ($allAssignments as $assignment) {
            $clinicId = (int) ($assignment->clinic_id ?? 0);
            $key = 'clinic:' . $clinicId;

            // Назначение не создаёт новую вкладку само по себе.
            // Вкладку разрешает только подтверждённый визит к психиатру.
            if (!isset($clinicTabMap[$key])) {
                continue;
            }

            $clinicTabMap[$key]['total']++;

            if ($assignment->status === PatientMedicationAssignment::STATUS_ACTIVE) {
                $clinicTabMap[$key]['active_count']++;
            } else {
                $clinicTabMap[$key]['history_count']++;
            }
        }

        $clinicTabs = collect(array_values($clinicTabMap))
            ->sortBy(static function (array $tab) use ($priorityClinicId): string {
                $isPriority = $priorityClinicId !== null
                    && (int) ($tab['clinic_id'] ?? 0) === $priorityClinicId;

                return ($isPriority ? '0-' : '1-')
                    . mb_strtolower((string) $tab['name']);
            })
            ->values();

        $showClinicTabs = $clinicTabs->count() > 1;
        $selectedClinicKey = null;

        if ($showClinicTabs) {
            $requestedClinicId = trim((string) $request->query('clinic_id', ''));

            if ($requestedClinicId === 'unknown') {
                $requestedKey = 'unknown';
            } elseif ($requestedClinicId !== ''
                && ctype_digit($requestedClinicId)
                && (int) $requestedClinicId > 0
            ) {
                $requestedKey = 'clinic:' . (int) $requestedClinicId;
            } else {
                $requestedKey = null;
            }

            $requestedExists = $requestedKey !== null
                && $clinicTabs->contains(
                    static fn (array $tab): bool => $tab['key'] === $requestedKey
                );

            if ($requestedExists) {
                $selectedClinicKey = $requestedKey;
            } else {
                $priorityKey = $priorityClinicId
                    ? 'clinic:' . $priorityClinicId
                    : null;

                $selectedClinicKey = $priorityKey
                    && $clinicTabs->contains(
                        static fn (array $tab): bool => $tab['key'] === $priorityKey
                    )
                        ? $priorityKey
                        : $clinicTabs->first()['key'];
            }

            $allAssignments = $allAssignments
                ->filter(static function (PatientMedicationAssignment $assignment) use ($selectedClinicKey): bool {
                    $clinicId = (int) ($assignment->clinic_id ?? 0);
                    $key = $clinicId > 0
                        ? 'clinic:' . $clinicId
                        : 'unknown';

                    return $key === $selectedClinicKey;
                })
                ->values();
        }

        return view('patients.assignments.index', [
            'patient' => $patient,
            'activeAssignments' => $allAssignments->where(
                'status',
                PatientMedicationAssignment::STATUS_ACTIVE
            )->values(),
            'historyAssignments' => $allAssignments->where(
                'status',
                '!=',
                PatientMedicationAssignment::STATUS_ACTIVE
            )->values(),
            'clinicTabs' => $clinicTabs,
            'showClinicTabs' => $showClinicTabs,
            'selectedClinicKey' => $selectedClinicKey,
            'priorityClinicMemberships' => $priorityClinicMemberships,
            'priorityClinicId' => $priorityClinicId,
            'showPriorityClinicSelector' => $priorityClinicMemberships->count() > 1,
        ]);
    }

    public function setPriorityClinic(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        /** @var Patient $patient */
        $patient = $user->patient()->firstOrFail();

        $data = $request->validate([
            'priority_clinic_id' => ['required', 'integer'],
        ]);

        $allowedClinicIds = $this->psychiatryVisitedClinicIds($patient);

        if (!in_array((int) $data['priority_clinic_id'], $allowedClinicIds, true)) {
            throw ValidationException::withMessages([
                'priority_clinic_id' => 'Выбрать можно только клинику, в которой состоялся приём у психиатра.',
            ]);
        }

        DB::transaction(function () use ($patient, $data): void {
            // Lock this patient's memberships to prevent two simultaneous
            // preference changes from leaving multiple primary clinics.
            $memberships = DB::table('patient_clinics')
                ->where('patient_id', (int) $patient->id)
                ->lockForUpdate()
                ->get(['id', 'clinic_id', 'is_active']);

            $target = $memberships->first(
                static fn ($membership): bool =>
                    (int) $membership->clinic_id === (int) $data['priority_clinic_id']
                    && (bool) $membership->is_active
            );

            if (!$target) {
                throw ValidationException::withMessages([
                    'priority_clinic_id' => 'Выбранная клиника больше не связана с вашей медицинской картой.',
                ]);
            }

            $now = now();

            DB::table('patient_clinics')
                ->where('patient_id', (int) $patient->id)
                ->update([
                    'is_primary' => false,
                    'updated_at' => $now,
                ]);

            DB::table('patient_clinics')
                ->where('id', (int) $target->id)
                ->update([
                    'is_primary' => true,
                    'updated_at' => $now,
                ]);
        });

        return redirect()
            ->route('patients.assignments.index')
            ->with('success', 'Приоритетная клиника сохранена.');
    }

    /**
     * Возвращает клиники, где у пациента подтверждён состоявшийся приём
     * у врача, в специальности которого явно указана психиатрия.
     *
     * Подтверждение визита:
     * - medical_visit_confirmations.outcome = held;
     * - или medical_encounters.consultation_status = completed;
     * - или событие medical_visit_events.consultation_confirmed.
     *
     * Если таблицы/поля подтверждения отсутствуют, доступ не расширяем:
     * возвращаем пустой список.
     */
    private function psychiatryVisitedClinicIds(Patient $patient): array
    {
        if (
            !Schema::hasTable('registry_records')
            || !Schema::hasTable('doctors')
        ) {
            return [];
        }

        foreach (['id', 'patient_id', 'doctor_id', 'clinic_id'] as $column) {
            if (!Schema::hasColumn('registry_records', $column)) {
                return [];
            }
        }

        $doctorSpecialityColumns = array_values(array_filter(
            ['status', 'profession', 'accreditation_speciality'],
            static fn (string $column): bool => Schema::hasColumn('doctors', $column)
        ));

        if ($doctorSpecialityColumns === []) {
            return [];
        }

        $hasConfirmations = Schema::hasTable('medical_visit_confirmations')
            && Schema::hasColumn('medical_visit_confirmations', 'registry_record_id')
            && Schema::hasColumn('medical_visit_confirmations', 'outcome');

        $hasEncounters = Schema::hasTable('medical_encounters')
            && Schema::hasColumn('medical_encounters', 'registry_record_id')
            && Schema::hasColumn('medical_encounters', 'consultation_status');

        $hasEvents = Schema::hasTable('medical_visit_events')
            && Schema::hasColumn('medical_visit_events', 'registry_record_id')
            && Schema::hasColumn('medical_visit_events', 'event_type');

        if (!$hasConfirmations && !$hasEncounters && !$hasEvents) {
            return [];
        }

        $query = DB::table('registry_records as rr')
            ->join('doctors as d', 'd.id', '=', 'rr.doctor_id')
            ->where('rr.patient_id', (int) $patient->id)
            ->whereNotNull('rr.clinic_id');

        if (Schema::hasColumn('registry_records', 'deleted_at')) {
            $query->whereNull('rr.deleted_at');
        }

        $query->where(function ($specialityQuery) use ($doctorSpecialityColumns): void {
            foreach ($doctorSpecialityColumns as $column) {
                $specialityQuery->orWhereRaw(
                    "COALESCE(d.{$column}, '') ILIKE ?",
                    ['%психиатр%']
                );
            }
        });

        $query->where(function ($visitQuery) use (
            $hasConfirmations,
            $hasEncounters,
            $hasEvents
        ): void {
            $hasCondition = false;

            if ($hasConfirmations) {
                $visitQuery->orWhereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('medical_visit_confirmations as mvc')
                        ->whereColumn('mvc.registry_record_id', 'rr.id')
                        ->where('mvc.outcome', 'held');
                });

                $hasCondition = true;
            }

            if ($hasEncounters) {
                $visitQuery->orWhereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('medical_encounters as me')
                        ->whereColumn('me.registry_record_id', 'rr.id')
                        ->where('me.consultation_status', 'completed');
                });

                $hasCondition = true;
            }

            if ($hasEvents) {
                $visitQuery->orWhereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('medical_visit_events as mve')
                        ->whereColumn('mve.registry_record_id', 'rr.id')
                        ->where('mve.event_type', 'consultation_confirmed');

                    if (Schema::hasColumn('medical_visit_events', 'source')) {
                        $subQuery->where('mve.source', 'clinician');
                    }
                });

                $hasCondition = true;
            }

            if (!$hasCondition) {
                $visitQuery->whereRaw('1 = 0');
            }
        });

        return $query
            ->distinct()
            ->pluck('rr.clinic_id')
            ->map(static fn ($clinicId): int => (int) $clinicId)
            ->filter(static fn (int $clinicId): bool => $clinicId > 0)
            ->values()
            ->all();
    }
}
