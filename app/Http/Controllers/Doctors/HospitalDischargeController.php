<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\HospitalBedAssignment;
use App\Models\HospitalDepartment;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Throwable;

class HospitalDischargeController extends Controller
{
    public function index(Request $request): View
    {
        [$doctor, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $departmentId = max(0, $request->integer('department_id'));
        $search = trim((string) $request->query('q', ''));
        $listMode = (string) $request->query('status', 'active');

        if (!in_array($listMode, ['active', 'released_today'], true)) {
            $listMode = 'active';
        }

        $departments = HospitalDepartment::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('number')
            ->orderBy('name')
            ->get(['id', 'number', 'name']);

        $clinicAssignments = HospitalBedAssignment::query()
            ->whereHas('department', fn (Builder $department): Builder =>
                $department->where('clinic_id', $clinicId));

        $activeCount = (clone $clinicAssignments)
            ->whereNull('released_at')
            ->count();

        $admittedTodayCount = (clone $clinicAssignments)
            ->whereNull('released_at')
            ->whereDate('assigned_at', today())
            ->count();

        $longStayCount = (clone $clinicAssignments)
            ->whereNull('released_at')
            ->where('assigned_at', '<=', now()->subDays(7))
            ->count();

        $releasedTodayCount = (clone $clinicAssignments)
            ->whereNotNull('released_at')
            ->whereDate('released_at', today())
            ->count();

        $assignmentsQuery = HospitalBedAssignment::query()
            ->with([
                'patient',
                'bed.room.department',
                'department',
            ])
            ->whereHas('department', fn (Builder $department): Builder =>
                $department->where('clinic_id', $clinicId))
            ->when(
                $listMode === 'active',
                static fn (Builder $query): Builder =>
                    $query->whereNull('released_at'),
                static fn (Builder $query): Builder =>
                    $query
                        ->whereNotNull('released_at')
                        ->whereDate('released_at', today())
            )
            ->when(
                $departmentId > 0,
                static fn (Builder $query): Builder =>
                    $query->where('department_id', $departmentId)
            )
            ->when(
                $search !== '',
                static function (Builder $query) use ($search): Builder {
                    $like = '%' . $search . '%';

                    return $query->whereHas(
                        'patient',
                        static function (Builder $patientQuery) use ($like): void {
                            $patientQuery->where(
                                static function (Builder $nameQuery) use ($like): void {
                                    $nameQuery
                                        ->where('surname', 'ilike', $like)
                                        ->orWhere('name', 'ilike', $like)
                                        ->orWhere('patronym', 'ilike', $like)
                                        ->orWhereRaw(
                                            "CONCAT_WS(' ', surname, name, patronym) ILIKE ?",
                                            [$like]
                                        );
                                }
                            );
                        }
                    );
                }
            )
            ->orderByDesc(
                $listMode === 'active'
                    ? 'assigned_at'
                    : 'released_at'
            );

        $assignments = $assignmentsQuery
            ->paginate(20)
            ->withQueryString();

        $assignments->setCollection(
            $assignments->getCollection()
                ->map(function (HospitalBedAssignment $assignment): array {
                    $patient = $assignment->patient;
                    $bed = $assignment->bed;
                    $room = $bed?->room;
                    $department = $assignment->department;
                    $assignedAt = $assignment->assigned_at
                        ? Carbon::parse($assignment->assigned_at)
                        : null;
                    $releasedAt = $assignment->released_at
                        ? Carbon::parse($assignment->released_at)
                        : null;

                    return [
                        'id' => (int) $assignment->id,
                        'patient_id' => (int) ($patient?->id ?? 0),
                        'patient_name' => trim(implode(' ', array_filter([
                            $patient?->surname,
                            $patient?->name,
                            $patient?->patronym,
                        ]))) ?: 'Пациент',
                        'birth_at' => $this->formatDate($patient?->birth_at),
                        'department' => (string) ($department?->name ?? 'Отделение не указано'),
                        'department_number' => (string) ($department?->number ?? ''),
                        'room' => (string) ($room?->number ?? '—'),
                        'bed' => (string) ($bed?->number ?? '—'),
                        'without_bed' => $bed === null,
                        'assigned_at' => $assignedAt?->format('d.m.Y H:i') ?? '—',
                        'released_at' => $releasedAt?->format('d.m.Y H:i'),
                        'days' => $assignedAt
                            ? max(1, $assignedAt->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1)
                            : null,
                        'medical_card_url' => $patient
                            ? route(
                                'doctors.patients.medical_card',
                                ['patient' => $patient->id]
                            )
                            : null,
                    ];
                })
                ->values()
        );

        return view('doctors.inpatient.discharges', [
            'doctor' => $doctor,
            'clinic' => $clinic,
            'departments' => $departments,
            'assignments' => $assignments,
            'activeCount' => $activeCount,
            'admittedTodayCount' => $admittedTodayCount,
            'longStayCount' => $longStayCount,
            'releasedTodayCount' => $releasedTodayCount,
            'departmentId' => $departmentId,
            'search' => $search,
            'listMode' => $listMode,
        ]);
    }

    /** Discharge a bedless admission without passing an invented bed ID to HospitalBedRelease. */
    public function dischargeCorridor(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $data = $request->validate([
            'assignment_id' => ['required', 'integer', 'min:1'],
            'patient_id' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data, $clinic): void {
            $patient = Patient::query()->whereKey((int) $data['patient_id'])
                ->lockForUpdate()->firstOrFail();
            $assignment = HospitalBedAssignment::query()
                ->whereKey((int) $data['assignment_id'])
                ->where('patient_id', (int) $patient->id)
                ->whereNull('bed_id')
                ->whereNull('released_at')
                ->whereHas('department', fn (Builder $department): Builder =>
                    $department->where('clinic_id', (int) $clinic->id))
                ->lockForUpdate()->firstOrFail();

            $assignment->released_at = now();
            $assignment->save();
        }, 3);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Пациент выписан из стационара.']);
        }
        return redirect()->route('doctors.inpatient.discharges.index')
            ->with('success', 'Пациент выписан из стационара.');
    }

    private function currentInpatientClinic(): array
    {
        $doctor = auth()->user()?->doctor;

        abort_unless(
            $doctor && (int) $doctor->clinic_id > 0,
            403
        );

        $clinic = Clinic::findOrFail((int) $doctor->clinic_id);

        abort_unless(
            (string) $clinic->clinic_type === Clinic::TYPE_INPATIENT,
            404
        );

        return [$doctor, $clinic];
    }

    private function formatDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }
}
