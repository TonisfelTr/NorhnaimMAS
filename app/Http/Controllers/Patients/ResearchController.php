<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResearchController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        /** @var Patient $patient */
        $patient = $user->patient()->firstOrFail();

        $clinicIds = $this->psychiatryVisitedClinicIds($patient);

        $clinicMemberships = $patient->clinicMemberships()
            ->where('is_active', true)
            ->whereIn('clinic_id', $clinicIds)
            ->with('clinic')
            ->get()
            ->filter(static fn ($membership): bool => $membership->clinic !== null)
            ->sortBy(static fn ($membership): string => sprintf(
                '%d-%s',
                $membership->is_primary ? 0 : 1,
                mb_strtolower((string) $membership->clinic->name)
            ))
            ->values();

        $availableClinicIds = $clinicMemberships
            ->pluck('clinic_id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $primaryMembership = $clinicMemberships->firstWhere('is_primary', true)
            ?? $clinicMemberships->first();

        $priorityClinicId = $primaryMembership
            ? (int) $primaryMembership->clinic_id
            : null;

        $requestedClinicId = filter_var(
            $request->query('clinic_id'),
            FILTER_VALIDATE_INT
        );

        $selectedClinicId = $requestedClinicId !== false
            && $requestedClinicId !== null
            && in_array((int) $requestedClinicId, $availableClinicIds, true)
                ? (int) $requestedClinicId
                : $priorityClinicId;

        $laboratoryResearches = $this->loadLaboratoryResearches(
            $patient,
            $availableClinicIds
        );

        $psychiatricTests = $this->loadPsychiatricTests(
            $patient,
            $availableClinicIds
        );

        $clinicTabs = $clinicMemberships->map(function ($membership) use (
            $laboratoryResearches,
            $psychiatricTests
        ): array {
            $clinicId = (int) $membership->clinic_id;
            $laboratoryCount = $laboratoryResearches
                ->where('clinic_id', $clinicId)
                ->count();
            $testCount = $psychiatricTests
                ->where('clinic_id', $clinicId)
                ->count();

            return [
                'key' => 'clinic:' . $clinicId,
                'clinic_id' => $clinicId,
                'name' => (string) $membership->clinic->name,
                'laboratory_count' => $laboratoryCount,
                'test_count' => $testCount,
                'total' => $laboratoryCount + $testCount,
            ];
        })->values();

        if ($selectedClinicId) {
            $laboratoryResearches = $laboratoryResearches
                ->where('clinic_id', $selectedClinicId)
                ->values();

            $psychiatricTests = $psychiatricTests
                ->where('clinic_id', $selectedClinicId)
                ->values();
        } else {
            $laboratoryResearches = collect();
            $psychiatricTests = collect();
        }

        return view('patients.research.index', [
            'patient' => $patient,
            'clinicTabs' => $clinicTabs,
            'showClinicTabs' => $clinicTabs->count() > 1,
            'showPriorityClinicSelector' => $clinicMemberships->count() > 1,
            'clinicMemberships' => $clinicMemberships,
            'priorityClinicId' => $priorityClinicId,
            'selectedClinicId' => $selectedClinicId,
            'selectedClinicName' => $clinicMemberships
                ->firstWhere('clinic_id', $selectedClinicId)?->clinic?->name,
            'laboratoryResearches' => $laboratoryResearches,
            'psychiatricTests' => $psychiatricTests,
            'hasEligibleClinics' => $clinicMemberships->isNotEmpty(),
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
            ->route('patients.research.index')
            ->with('success', 'Приоритетная клиника сохранена.');
    }

    private function loadLaboratoryResearches(
        Patient $patient,
        array $clinicIds
    ): Collection {
        if (
            $clinicIds === []
            || !Schema::hasTable('lab_researches')
            || !Schema::hasTable('lab_research_results')
            || !Schema::hasTable('lab_parameters')
            || !Schema::hasTable('doctors')
            || !Schema::hasTable('clinics')
        ) {
            return collect();
        }

        $researches = DB::table('lab_researches as lr')
            ->join('doctors as d', 'd.id', '=', 'lr.doctor_id')
            ->leftJoin('clinics as c', 'c.id', '=', 'd.clinic_id')
            ->where('lr.patient_id', (int) $patient->id)
            ->whereIn('d.clinic_id', $clinicIds)
            ->select([
                'lr.id',
                'lr.patient_id',
                'lr.doctor_id',
                'd.clinic_id',
                'c.name as clinic_name',
                'd.name as doctor_name',
                'd.surname as doctor_surname',
                'd.patronym as doctor_patronym',
                'lr.laboratory',
                'lr.status',
                'lr.research_date',
                'lr.planned_at',
                'lr.created_at',
                'lr.sample_type',
            ])
            ->orderByDesc('lr.created_at')
            ->orderByDesc('lr.id')
            ->limit(300)
            ->get();

        if ($researches->isEmpty()) {
            return collect();
        }

        $researchIds = $researches->pluck('id')->map(
            static fn ($id): int => (int) $id
        )->all();

        $resultRows = DB::table('lab_research_results as r')
            ->join('lab_parameters as p', 'p.id', '=', 'r.lab_parameter_id')
            ->where('r.patient_id', (int) $patient->id)
            ->whereIn('r.lab_research_id', $researchIds)
            ->select([
                'r.id as result_id',
                'r.lab_research_id',
                'r.value',
                'r.lab_parameter_id',
                'p.name as parameter_name',
                'p.unit',
                'p.data_type',
                'p.normal_values',
                'p.group as parameter_group',
            ])
            ->orderBy('p.group')
            ->orderBy('p.name')
            ->orderBy('r.id')
            ->get();

        $parameterIds = $resultRows->pluck('lab_parameter_id')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $referenceRanges = collect();

        if (
            $parameterIds !== []
            && Schema::hasTable('lab_reference_ranges')
        ) {
            $referenceRanges = DB::table('lab_reference_ranges')
                ->whereIn('parameter_id', $parameterIds)
                ->get()
                ->groupBy('parameter_id');
        }

        $age = $this->patientAge($patient);
        $sex = $this->patientSex($patient);

        $preparedResults = $resultRows->map(function ($row) use (
            $referenceRanges,
            $age,
            $sex
        ): array {
            $ranges = $referenceRanges->get((int) $row->lab_parameter_id, collect())
                ->map(fn ($range): array => (array) $range)
                ->all();

            $matchingRanges = $this->matchingReferenceRanges(
                $ranges,
                $age,
                $sex
            );

            if ($matchingRanges === []) {
                $normalValues = $this->decodeJson($row->normal_values);
                $normalRanges = $this->normalizeNormalValues($normalValues);
                $matchingRanges = $this->matchingReferenceRanges(
                    $normalRanges,
                    $age,
                    $sex
                );
            }

            $referenceText = $this->formatReferenceRanges($matchingRanges);
            $deviation = $this->resultDeviation(
                $row->value,
                $row->data_type,
                $matchingRanges
            );

            return [
                'result_id' => (int) $row->result_id,
                'lab_research_id' => (int) $row->lab_research_id,
                'parameter_name' => (string) $row->parameter_name,
                'parameter_group' => (string) ($row->parameter_group ?? ''),
                'value' => (string) ($row->value ?? ''),
                'unit' => (string) ($row->unit ?? ''),
                'reference_text' => $referenceText,
                'deviation' => $deviation,
                'deviation_label' => match ($deviation) {
                    'low' => 'Ниже нормы',
                    'high' => 'Выше нормы',
                    'normal' => 'В пределах нормы',
                    default => '',
                },
            ];
        })->groupBy('lab_research_id');

        return $researches->map(function ($research) use ($preparedResults): array {
            $status = mb_strtolower((string) $research->status);
            $statusLabel = match ($status) {
                'ready' => 'Готов',
                'processing' => 'В обработке',
                'cancelled' => 'Отменён',
                default => $status !== '' ? $status : 'Статус не указан',
            };

            $date = $research->research_date ?: $research->created_at;

            return [
                'id' => (int) $research->id,
                'clinic_id' => (int) $research->clinic_id,
                'clinic_name' => (string) ($research->clinic_name ?? ''),
                'title' => (string) ($research->laboratory ?: 'Лабораторное исследование'),
                'status' => $status,
                'status_label' => $statusLabel,
                'date_label' => $this->formatDate($date),
                'planned_at_label' => $this->formatDate($research->planned_at),
                'sample_type' => (string) ($research->sample_type ?? ''),
                'doctor_name' => trim(
                    (string) ($research->doctor_surname ?? '')
                    . ' '
                    . (string) ($research->doctor_name ?? '')
                    . ' '
                    . (string) ($research->doctor_patronym ?? '')
                ),
                'results' => $preparedResults->get((int) $research->id, collect())->values()->all(),
            ];
        })->values();
    }

    private function loadPsychiatricTests(
        Patient $patient,
        array $clinicIds
    ): Collection {
        if (
            $clinicIds === []
            || !Schema::hasTable('tests')
            || !Schema::hasTable('doctors')
        ) {
            return collect();
        }

        $assignments = collect();
        $assignmentSessionIds = [];

        if (Schema::hasTable('test_assignments')) {
            $query = DB::table('test_assignments as ta')
                ->leftJoin('tests as t', 't.id', '=', 'ta.test_id')
                ->where('ta.patient_id', (int) $patient->id);

            if (Schema::hasTable('test_sessions') && Schema::hasColumn('test_assignments', 'session_id')) {
                $query->leftJoin('test_sessions as ts', 'ts.id', '=', 'ta.session_id')
                    ->addSelect([
                        'ts.status as session_status',
                        'ts.completed_at as session_completed_at',
                        'ts.clinician_id as session_clinician_id',
                    ]);
            }

            $select = [
                'ta.id as assignment_id',
                'ta.test_id',
                'ta.clinician_id as assignment_clinician_id',
                'ta.status as assignment_status',
                'ta.due_at',
                'ta.started_at',
                'ta.finished_at',
                'ta.session_id',
                'ta.context as assignment_context',
                'ta.created_at as assigned_at',
                't.name as test_name',
                't.code as test_code',
                't.description as test_description',
                't.instructions as test_instructions',
                't.estimated_minutes',
            ];

            $assignments = $query
                ->addSelect($select)
                ->orderByDesc('ta.created_at')
                ->limit(200)
                ->get();

            $assignmentSessionIds = $assignments->pluck('session_id')
                ->filter()
                ->map(static fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $standaloneSessions = collect();

        if (Schema::hasTable('test_sessions')) {
            $sessionQuery = DB::table('test_sessions as ts')
                ->leftJoin('tests as t', 't.id', '=', 'ts.test_id')
                ->where('ts.patient_id', (int) $patient->id)
                ->where(function ($sourceQuery): void {
                    $sourceQuery->whereNull('ts.context->source')
                        ->orWhere('ts.context->source', '!=', 'patient_self');
                });

            if ($assignmentSessionIds !== []) {
                $sessionQuery->whereNotIn('ts.id', $assignmentSessionIds);
            }

            $standaloneSessions = $sessionQuery
                ->select([
                    'ts.id as session_id',
                    'ts.test_id',
                    'ts.clinician_id as session_clinician_id',
                    'ts.status as session_status',
                    'ts.completed_at as session_completed_at',
                    'ts.created_at as assigned_at',
                    't.name as test_name',
                    't.code as test_code',
                    't.description as test_description',
                    't.instructions as test_instructions',
                    't.estimated_minutes',
                ])
                ->orderByDesc('ts.created_at')
                ->limit(200)
                ->get();
        }

        $allRows = collect();

        foreach ($assignments as $assignment) {
            $allRows->push([
                'kind' => 'assignment',
                'assignment_id' => $assignment->assignment_id,
                'session_id' => $assignment->session_id,
                'test_id' => $assignment->test_id,
                'assignment_clinician_id' => $assignment->assignment_clinician_id,
                'session_clinician_id' => $assignment->session_clinician_id ?? null,
                'assignment_status' => $assignment->assignment_status,
                'session_status' => $assignment->session_status ?? null,
                'completed_at' => $assignment->session_completed_at ?? null,
                'due_at' => $assignment->due_at,
                'assigned_at' => $assignment->assigned_at,
                'test_name' => $assignment->test_name,
                'test_code' => $assignment->test_code,
                'test_description' => $assignment->test_description,
                'test_instructions' => $assignment->test_instructions,
                'estimated_minutes' => $assignment->estimated_minutes,
                'context' => $assignment->assignment_context,
            ]);
        }

        foreach ($standaloneSessions as $session) {
            $allRows->push([
                'kind' => 'session',
                'assignment_id' => null,
                'session_id' => $session->session_id,
                'test_id' => $session->test_id,
                'assignment_clinician_id' => null,
                'session_clinician_id' => $session->session_clinician_id,
                'assignment_status' => null,
                'session_status' => $session->session_status,
                'completed_at' => $session->session_completed_at,
                'due_at' => null,
                'assigned_at' => $session->assigned_at,
                'test_name' => $session->test_name,
                'test_code' => $session->test_code,
                'test_description' => $session->test_description,
                'test_instructions' => $session->test_instructions,
                'estimated_minutes' => $session->estimated_minutes,
                'context' => null,
            ]);
        }

        if ($allRows->isEmpty()) {
            return collect();
        }

        $clinicianIds = $allRows->flatMap(static fn (array $row): array => array_filter([
            is_numeric($row['assignment_clinician_id']) ? (int) $row['assignment_clinician_id'] : null,
            is_numeric($row['session_clinician_id']) ? (int) $row['session_clinician_id'] : null,
        ]))->unique()->values()->all();

        // В назначениях clinician_id сохраняется как ID авторизованного пользователя.
        // Поэтому клинику определяем только через doctors.user_id, не угадывая по совпадающим ID.
        $doctorsByUserId = collect();

        if ($clinicianIds !== []) {
            $doctorsByUserId = DB::table('doctors')
                ->whereIn('user_id', $clinicianIds)
                ->get(['id', 'user_id', 'clinic_id', 'name', 'surname', 'patronym'])
                ->keyBy('user_id');
        }

        $sessionIds = $allRows->pluck('session_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $testResults = collect();

        if ($sessionIds !== [] && Schema::hasTable('test_results')) {
            $resultQuery = DB::table('test_results as tr')
                ->whereIn('tr.session_id', $sessionIds);

            if (Schema::hasTable('test_keys')) {
                $resultQuery->leftJoin('test_keys as tk', 'tk.id', '=', 'tr.key_id')
                    ->addSelect('tk.title as result_title');
            }

            $testResults = $resultQuery
                ->addSelect([
                    'tr.id',
                    'tr.session_id',
                    'tr.score',
                    'tr.range_text',
                    'tr.interpretation',
                ])
                ->orderBy('tr.id')
                ->get()
                ->groupBy('session_id');
        }

        return $allRows->map(function (array $row) use (
            $doctorsByUserId,
            $clinicIds,
            $testResults
        ): ?array {
            $clinicianId = $row['assignment_clinician_id']
                ?? $row['session_clinician_id']
                ?? null;

            $doctor = null;

            if (is_numeric($clinicianId)) {
                $doctor = $doctorsByUserId->get((int) $clinicianId);
            }

            $context = $this->decodeJson($row['context']);
            $contextClinicId = data_get($context, 'clinic_id')
                ?? data_get($context, 'clinic.id');

            $clinicId = $doctor?->clinic_id
                ? (int) $doctor->clinic_id
                : (is_numeric($contextClinicId) ? (int) $contextClinicId : null);

            if (!$clinicId || !in_array($clinicId, $clinicIds, true)) {
                return null;
            }

            $status = mb_strtolower((string) (
                $row['session_status']
                ?: $row['assignment_status']
                ?: 'assigned'
            ));

            $isCancelled = $status === 'cancelled';
            $isCompleted = !$isCancelled && (
                in_array($status, ['complete', 'completed', 'finished'], true)
                || !empty($row['completed_at'])
            );

            $statusLabel = match (true) {
                $isCancelled => 'Отменён',
                in_array($status, ['assigned', 'created', 'pending'], true) => 'Ожидает прохождения',
                in_array($status, ['started', 'in_progress'], true) => 'В процессе',
                in_array($status, ['submitted', 'in_review'], true) => 'На проверке',
                $isCompleted => 'Завершён',
                default => $status !== '' ? $status : 'Статус не указан',
            };

            $sessionId = is_numeric($row['session_id'])
                ? (int) $row['session_id']
                : null;

            $results = $isCompleted && $sessionId
                ? $testResults->get($sessionId, collect())->map(static fn ($result): array => [
                    'title' => (string) ($result->result_title ?? 'Итог'),
                    'score' => (string) ($result->score ?? ''),
                    'range' => (string) ($result->range_text ?? ''),
                    'interpretation' => (string) ($result->interpretation ?? ''),
                ])->values()->all()
                : [];

            return [
                'assignment_id' => $row['assignment_id'],
                'session_id' => $sessionId,
                'clinic_id' => $clinicId,
                'clinic_name' => '',
                'test_name' => (string) ($row['test_name'] ?? 'Психиатрический тест'),
                'test_code' => (string) ($row['test_code'] ?? ''),
                'description' => (string) ($row['test_description'] ?? ''),
                'instructions' => (string) ($row['test_instructions'] ?? ''),
                'estimated_minutes' => $row['estimated_minutes'],
                'status' => $status,
                'status_label' => $statusLabel,
                'is_completed' => $isCompleted,
                'assigned_at_label' => $this->formatDate($row['assigned_at']),
                'due_at_label' => $this->formatDate($row['due_at']),
                'completed_at_label' => $this->formatDate($row['completed_at']),
                'doctor_name' => $doctor
                    ? trim(
                        (string) $doctor->surname
                        . ' ' . (string) $doctor->name
                        . ' ' . (string) ($doctor->patronym ?? '')
                    )
                    : '',
                'results' => $results,
            ];
        })->filter()->values();
    }

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
            if ($hasConfirmations) {
                $visitQuery->orWhereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('medical_visit_confirmations as mvc')
                        ->whereColumn('mvc.registry_record_id', 'rr.id')
                        ->where('mvc.outcome', 'held');
                });
            }

            if ($hasEncounters) {
                $visitQuery->orWhereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('medical_encounters as me')
                        ->whereColumn('me.registry_record_id', 'rr.id')
                        ->where('me.consultation_status', 'completed');
                });
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

    private function patientAge(Patient $patient): ?int
    {
        foreach (['birth_at', 'birth_date', 'date_of_birth', 'dob'] as $field) {
            $value = $patient->{$field} ?? null;

            if (!$value) {
                continue;
            }

            try {
                return Carbon::parse($value)->age;
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function patientSex(Patient $patient): ?string
    {
        $value = mb_strtolower(trim((string) (
            $patient->sex ?? $patient->gender ?? ''
        )));

        return match ($value) {
            'm', 'male', 'man', 'м', 'муж', 'мужской' => 'm',
            'f', 'female', 'woman', 'ж', 'жен', 'женский' => 'f',
            default => null,
        };
    }

    private function normalizeNormalValues($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        if (array_key_exists('min_value', $value) || array_key_exists('max_value', $value)) {
            return [$value];
        }

        if (array_key_exists('min', $value) || array_key_exists('max', $value)) {
            return [$value];
        }

        $normalized = [];

        foreach ($value as $normalValue) {
            if (is_array($normalValue)) {
                $normalized[] = $normalValue;
            } elseif (is_string($normalValue) && trim($normalValue) !== '') {
                $normalized[] = ['text' => trim($normalValue)];
            }
        }

        return $normalized;
    }

    private function matchingReferenceRanges(
        array $ranges,
        ?int $age,
        ?string $sex
    ): array {
        $matched = [];

        foreach ($ranges as $range) {
            $rangeSex = mb_strtolower(trim((string) ($range['sex'] ?? 'any')));
            $rangeSex = match ($rangeSex) {
                'm', 'male', 'man', 'м', 'муж', 'мужской' => 'm',
                'f', 'female', 'woman', 'ж', 'жен', 'женский' => 'f',
                default => $rangeSex,
            };

            if (
                $sex !== null
                && !in_array($rangeSex, ['', 'any', 'all', $sex], true)
            ) {
                continue;
            }

            $ageMin = $range['age_min_y'] ?? null;
            $ageMax = $range['age_max_y'] ?? null;

            if ($age !== null) {
                if ($ageMin !== null && $age < (int) $ageMin) {
                    continue;
                }
                if ($ageMax !== null && $age > (int) $ageMax) {
                    continue;
                }
            }

            $rawMin = $range['min'] ?? $range['min_value'] ?? null;
            $rawMax = $range['max'] ?? $range['max_value'] ?? null;
            $min = $this->numericResultValue($rawMin);
            $max = $this->numericResultValue($rawMax);

            if (
                ($rawMin !== null && $rawMin !== '' && $min === null)
                || ($rawMax !== null && $rawMax !== '' && $max === null)
                || ($min !== null && $max !== null && $min > $max)
            ) {
                continue;
            }

            if ($min !== null || $max !== null) {
                $matched[] = ['min' => $min, 'max' => $max];
            } elseif (isset($range['text']) && is_string($range['text'])) {
                $text = trim($range['text']);

                if ($text !== '') {
                    $matched[] = ['text' => $text];
                }
            }
        }

        return $matched;
    }

    private function formatReferenceRanges(array $ranges): string
    {
        $parts = [];

        foreach ($ranges as $range) {
            if (isset($range['text'])) {
                $parts[] = $range['text'];
                continue;
            }

            $min = str_replace('.', ',', $this->formatNumber($range['min'] ?? null));
            $max = str_replace('.', ',', $this->formatNumber($range['max'] ?? null));

            if ($min !== '' && $max !== '') {
                $parts[] = $min . '–' . $max;
            } elseif ($min !== '') {
                $parts[] = 'от ' . $min;
            } elseif ($max !== '') {
                $parts[] = 'до ' . $max;
            }
        }

        return implode('; ', array_unique($parts));
    }

    private function resultDeviation(
        $value,
        ?string $dataType,
        array $ranges
    ): ?string {
        $numeric = $this->numericResultValue($value);

        if ($numeric === null || $dataType !== 'numeric' || $ranges === []) {
            return null;
        }

        $states = [];

        foreach ($ranges as $range) {
            if (isset($range['text'])) {
                return null;
            }

            $min = $this->numericResultValue($range['min'] ?? null);
            $max = $this->numericResultValue($range['max'] ?? null);

            // Пустой референс не является числовым интервалом.
            if ($min === null && $max === null) {
                continue;
            }

            if ($min !== null && $numeric < $min) {
                $states[] = 'low';
            } elseif ($max !== null && $numeric > $max) {
                $states[] = 'high';
            } else {
                $states[] = 'normal';
            }
        }

        $states = array_values(array_unique($states));

        // Если без пола/возраста подходят разные референсные интервалы,
        // не ставим пациенту ложный маркер отклонения.
        return count($states) === 1 ? $states[0] : null;
    }

    private function numericResultValue($value): ?float
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        if (!preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?$/D', $normalized)) {
            return null;
        }

        $numeric = (float) $normalized;

        return is_finite($numeric) ? $numeric : null;
    }

    private function decodeJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return [];
    }

    private function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        return rtrim(rtrim(sprintf('%.4f', (float) $value), '0'), '.');
    }

    private function formatDate($value): string
    {
        if (!$value) {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
