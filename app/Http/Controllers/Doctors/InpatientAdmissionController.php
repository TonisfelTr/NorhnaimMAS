<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\HospitalBed;
use App\Models\HospitalBedAssignment;
use App\Models\HospitalDepartment;
use App\Models\HospitalRoom;
use App\Models\Patient;
use App\Support\HospitalDepartmentAdmissionPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * Стационар: приём пациента с обязательным выбором отделения, палаты и койки.
 * Использует существующие hospital_bed_assignments; новой таблицы не нужно.
 */
class InpatientAdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $clinic = $this->inpatientClinic($request);
        $clinicId = (int) $clinic->id;
        $search = trim((string) $request->query('q', ''));
        $departmentId = max(0, $request->integer('department_id'));

        $departments = HospitalDepartment::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'number']);

        $activeAssignments = HospitalBedAssignment::query()
            ->whereNull('released_at')
            ->whereHas('bed', fn (Builder $query): Builder => $query
                ->where('clinic_id', $clinicId)
                ->whereHas('room', fn (Builder $room): Builder => $room
                    ->where('clinic_id', $clinicId)))
            ->with(['patient', 'bed.room.department.building'])
            ->when(
                $departmentId > 0,
                fn (Builder $query): Builder => $query->whereHas(
                    'bed.room',
                    fn (Builder $room): Builder => $room->where('department_id', $departmentId)
                )
            )
            ->when(
                $search !== '',
                fn (Builder $query): Builder => $query->whereHas(
                    'patient',
                    function (Builder $patientQuery) use ($search): void {
                        $pattern = '%' . $search . '%';
                        $patientQuery->where(function (Builder $names) use ($pattern): void {
                            $names->where('surname', 'ilike', $pattern)
                                ->orWhere('name', 'ilike', $pattern)
                                ->orWhere('patronym', 'ilike', $pattern)
                                ->orWhereRaw(
                                    "CONCAT_WS(' ', surname, name, patronym) ILIKE ?",
                                    [$pattern]
                                );
                        });
                    }
                )
            )
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');

        $admissions = $activeAssignments
            ->paginate(20, ['*'], 'admissions_page')
            ->withQueryString();

        $availableBeds = HospitalBed::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->whereHas('room', fn (Builder $room): Builder => $room
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->whereHas('department', fn (Builder $department): Builder => $department
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)))
            ->whereDoesntHave('currentAssignment')
            ->count();

        return view('doctors.reception.inpatient', [
            'clinic' => $clinic,
            'admissions' => $admissions,
            'departments' => $departments,
            'admissionsCount' => HospitalBedAssignment::query()
                ->whereNull('released_at')
                ->whereHas('bed', fn (Builder $bed): Builder => $bed->where('clinic_id', $clinicId))
                ->count(),
            'admittedTodayCount' => HospitalBedAssignment::query()
                ->whereNull('released_at')
                ->whereDate('assigned_at', Carbon::today())
                ->whereHas('bed', fn (Builder $bed): Builder => $bed->where('clinic_id', $clinicId))
                ->count(),
            'availableBeds' => $availableBeds,
            'search' => $search,
            'selectedDepartmentId' => $departmentId,
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $isPlacementEdit = $this->isPlacementEdit($request);
        $clinic = $this->inpatientClinic($request, $isPlacementEdit);
        $clinicId = (int) $clinic->id;

        // Лениво загружаем сохранённую схему только выбранной палаты.
        // Маршрут /options остаётся прежним; пациентов других коек не выдаём.
        if (!$isPlacementEdit && $request->filled('room_id')) {
            $roomId = (int) $request->validate([
                'room_id' => ['required', 'integer', 'min:1'],
            ])['room_id'];

            $room = HospitalRoom::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->whereHas('department', fn (Builder $department): Builder => $department
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true))
                ->with(['beds.currentAssignment', 'items'])
                ->findOrFail($roomId);

            return response()->json(['room_layout' => [
                'id' => (int) $room->id,
                'canvas_width' => max(1, (int) ($room->canvas_width ?: 900)),
                'canvas_height' => max(1, (int) ($room->canvas_height ?: 560)),
                'beds' => $room->beds->sortBy('number')->values()
                    ->map(static fn (HospitalBed $bed): array => [
                        'id' => (int) $bed->id,
                        'number' => (string) $bed->number,
                        'label' => (string) ($bed->label ?? ''),
                        'x' => (int) $bed->x,
                        'y' => (int) $bed->y,
                        'width' => (int) $bed->width,
                        'height' => (int) $bed->height,
                        'rotation' => round((float) $bed->rotation, 2),
                        'enabled' => (bool) $bed->enabled,
                        'occupied' => $bed->currentAssignment !== null,
                        'is_available' => (bool) $bed->enabled && $bed->currentAssignment === null,
                    ])->values(),
                'items' => $room->items->sortBy('sort_order')->values()
                    ->map(static fn ($item): array => [
                        'type' => (string) $item->type,
                        'label' => (string) ($item->label ?? ''),
                        'x' => (int) $item->x,
                        'y' => (int) $item->y,
                        'width' => (int) $item->width,
                        'height' => (int) $item->height,
                        'rotation' => round((float) $item->rotation, 2),
                    ])->values(),
            ]]);
        }

        $data = $request->validate($isPlacementEdit ? [
            'patient_id' => ['required', 'integer', 'min:1'],
            'assignment_id' => ['required', 'integer', 'min:1'],
            'current_bed_id' => ['required', 'integer', 'min:1'],
        ] : ['patient_id' => ['sometimes', 'nullable', 'integer', 'min:1']]);
        $placement = $isPlacementEdit ? $this->placementForEditing($clinicId, $data) : null;
        $patient = $placement?->patient ?? ($request->filled('patient_id')
            ? $this->scopedPatient($clinicId, $request->integer('patient_id'), false)
            : null);
        $currentAssignmentId = $placement ? (int) $placement->id : null;
        $currentBedId = $placement ? (int) $placement->bed_id : null;
        // Показываем свободные койки; собственную текущую койку разрешаем
        // выбрать повторно, чтобы не заставлять пациента менять размещение.
        // Койки, занятые ДРУГИМИ активными госпитализациями, исключены.
        $availableBeds = static fn ($beds) => $beds
            ->where('hospital_beds.clinic_id', $clinicId)
            ->where('enabled', true)
            ->whereDoesntHave('currentAssignment', function (Builder $active) use ($currentAssignmentId): void {
                if ($currentAssignmentId !== null) {
                    $active->whereKeyNot($currentAssignmentId);
                }
            });

        $rooms = HospitalRoom::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->whereHas('department', fn (Builder $query): Builder => $query
                ->where('clinic_id', $clinicId)
                ->where('enabled', true))
            ->whereHas('beds', $availableBeds)
            ->with([
                'department.building',
                'beds' => fn ($beds) => $availableBeds($beds)->orderBy('number'),
            ])
            ->orderBy('number')
            ->get();

        $departments = $rooms
            ->pluck('department')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->map(function (HospitalDepartment $department) use ($patient): array {
                $age = HospitalDepartmentAdmissionPolicy::restrictions($department);
                $restriction = null;
                if ($patient) {
                    try {
                        $this->validateDepartmentPatient($department, $patient);
                    } catch (ValidationException $exception) {
                        $restriction = collect($exception->errors())->flatten()->first();
                    }
                }

                return [
                    'id' => (int) $department->id,
                    'name' => (string) $department->name,
                    'sex_type' => (string) ($department->sex_type ?? 'mixed'),
                    'sex_type_label' => HospitalDepartmentAdmissionPolicy::sexLabel($department),
                    'profile' => (string) ($department->profile ?? ''),
                    'profile_category' => HospitalDepartmentAdmissionPolicy::profileCategory($department),
                    'building' => $department->building
                        ? trim('Корпус ' . ($department->building->number ?? '') . ' — ' . ($department->building->name ?? ''))
                        : 'Корпус не указан',
                    'is_pediatric' => ($age['group'] ?? null) === 'children',
                    'is_age_restricted' => $age !== null,
                    'age_group' => $age['group'] ?? null,
                    'age_group_label' => $age['label'] ?? null,
                    'age_min_inclusive' => $age['min_age_inclusive'] ?? null,
                    'age_limit_exclusive' => $age['max_age_exclusive'] ?? null,
                    'patient_eligible' => $patient ? $restriction === null : null,
                    'patient_restriction' => $restriction,
                ];
            });

        $response = [
            'departments' => $departments,
            'rooms' => $rooms->map(fn (HospitalRoom $room): array => [
                'id' => (int) $room->id,
                'department_id' => (int) $room->department_id,
                'label' => 'Палата №' . $room->number
                    . ($room->name ? ' — ' . $room->name : '')
                    . ($room->floor ? ' · этаж ' . $room->floor : ''),
                'free_beds_count' => $room->beds->filter(
                    fn (HospitalBed $bed): bool => $currentBedId === null || (int) $bed->id !== $currentBedId
                )->count(),
                'beds' => $room->beds->map(fn (HospitalBed $bed): array => [
                    'id' => (int) $bed->id,
                    'label' => 'Койка №' . $bed->number
                        . ($bed->label ? ' — ' . $bed->label : ''),
                    'is_current' => $currentBedId !== null && (int) $bed->id === $currentBedId,
                    'is_available' => $currentBedId === null || (int) $bed->id !== $currentBedId,
                ])->values(),
            ])->values(),
        ];
        if ($placement) {
            $response['placement'] = $this->placementPayload($placement);
        }

        return response()->json($response);
    }

    public function searchPatients(Request $request): JsonResponse
    {
        $clinicId = (int) $this->inpatientClinic($request)->id;
        $request->validate(['department_id' => ['sometimes', 'nullable', 'integer', 'min:1']]);
        $department = null;
        if ($request->filled('department_id')) {
            $department = HospitalDepartment::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->find($request->integer('department_id'));
            if (!$department) {
                throw ValidationException::withMessages([
                    'department_id' => 'Отделение не найдено или недоступно.',
                ]);
            }
        }

        $search = trim((string) $request->query('q', ''));
        if (mb_strlen($search) < 2) {
            return response()->json(['items' => []]);
        }

        $pattern = '%' . $search . '%';
        $patientsQuery = $this->scopePatientsToClinic(Patient::query(), $clinicId)
            ->where(function (Builder $query) use ($pattern): void {
                $query->where('surname', 'ilike', $pattern)
                    ->orWhere('name', 'ilike', $pattern)
                    ->orWhere('patronym', 'ilike', $pattern)
                    ->orWhereRaw("CONCAT_WS(' ', surname, name, patronym) ILIKE ?", [$pattern]);
            })
            ->whereNotIn('id', HospitalBedAssignment::query()
                ->whereNotNull('patient_id')
                ->whereNull('released_at')->select('patient_id'));

        $eligiblePatients = HospitalDepartmentAdmissionPolicy::applyToPatients($patientsQuery, $department);
        $patients = $eligiblePatients
            ->orderBy('surname')
            ->orderBy('name')
            ->limit(15)
            ->get();
        $age = HospitalDepartmentAdmissionPolicy::restrictions($department);

        return response()->json([
            'items' => $patients->map(function (Patient $patient): array {
                $birthAt = null;
                try {
                    $rawBirthAt = $patient->getAttributes()['birth_at'] ?? null;
                    $birthAt = $rawBirthAt ? Carbon::parse($rawBirthAt) : null;
                } catch (Throwable) {
                    // Старая некорректная дата не должна ломать общий поиск.
                }

                return [
                    'id' => (int) $patient->id,
                    'name' => trim(implode(' ', array_filter([
                        $patient->surname, $patient->name, $patient->patronym,
                    ]))),
                    'birth_at' => $birthAt?->format('d.m.Y') ?? '',
                    'birth_at_iso' => $birthAt?->format('Y-m-d'),
                ];
            })->values(),
            'is_pediatric' => ($age['group'] ?? null) === 'children',
            'is_age_restricted' => $age !== null,
            'age_group' => $age['group'] ?? null,
            'age_group_label' => $age['label'] ?? null,
            'age_min_inclusive' => $age['min_age_inclusive'] ?? null,
            'age_limit_exclusive' => $age['max_age_exclusive'] ?? null,
            'sex_type' => (string) ($department?->sex_type ?? 'mixed'),
            'sex_type_label' => HospitalDepartmentAdmissionPolicy::sexLabel($department),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $isPlacementEdit = $this->isPlacementEdit($request);
        $clinic = $this->inpatientClinic($request, $isPlacementEdit);
        $clinicId = (int) $clinic->id;
        if ($isPlacementEdit) {
            return $this->updatePlacement($request, $clinicId);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'department_id' => ['required', 'integer'],
            'room_id' => ['required', 'integer'],
            'bed_id' => ['required', 'integer'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($clinicId, $data): void {
                // Защита от двух одновременных оформлений одного пациента.
                $patient = $this->scopedPatient($clinicId, (int) $data['patient_id']);

                $bed = HospitalBed::query()
                    ->where('clinic_id', $clinicId)
                    ->where('room_id', (int) $data['room_id'])
                    ->where('enabled', true)
                    ->lockForUpdate()
                    ->find((int) $data['bed_id']);

                if (!$bed) {
                    throw ValidationException::withMessages([
                        'bed_id' => 'Койка не найдена или недоступна.',
                    ]);
                }

                $room = HospitalRoom::query()
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->where('department_id', (int) $data['department_id'])
                    ->lockForUpdate()
                    ->find((int) $bed->room_id);

                $department = HospitalDepartment::query()
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->lockForUpdate()
                    ->find((int) $data['department_id']);

                if (!$room || !$department) {
                    throw ValidationException::withMessages([
                        'room_id' => 'Палата или отделение недоступны.',
                    ]);
                }

                $this->validateDepartmentPatient($department, $patient);

                if (HospitalBedAssignment::query()
                    ->where('bed_id', $bed->id)
                    ->whereNull('released_at')
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'bed_id' => 'Койка уже занята. Выберите другую.',
                    ]);
                }

                if (HospitalBedAssignment::query()
                    ->where('patient_id', $patient->id)
                    ->whereNull('released_at')
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'patient_id' => 'Пациент уже размещён в стационаре.',
                    ]);
                }

                HospitalBedAssignment::create([
                    'bed_id' => $bed->id,
                    'patient_id' => $patient->id,
                    'assigned_at' => now(),
                    'comment' => isset($data['comment'])
                        ? trim((string) $data['comment']) ?: null
                        : null,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            // Частичные уникальные индексы PostgreSQL страхуют от гонок
            // с другими способами назначения койки.
            if (($exception->errorInfo[0] ?? null) === '23505') {
                throw ValidationException::withMessages([
                    'bed_id' => 'Койка занята или пациент уже размещён. Обновите список.',
                ]);
            }
            throw $exception;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Госпитализация оформлена: пациент размещён на койке.',
        ]);
    }

    private function isPlacementEdit(Request $request): bool
    {
        $request->validate([
            'intent' => ['sometimes', 'required', 'string', 'in:edit_placement'],
        ]);
        $isPlacementEdit = $request->input('intent') === 'edit_placement';
        if (!$isPlacementEdit && ($request->exists('assignment_id') || $request->exists('current_bed_id'))) {
            throw ValidationException::withMessages([
                'intent' => 'Не удалось определить действие с размещением. Обновите страницу.',
            ]);
        }

        return $isPlacementEdit;
    }

    private function placementForEditing(int $clinicId, array $data): HospitalBedAssignment
    {
        $placement = HospitalBedAssignment::query()
            ->whereKey((int) $data['assignment_id'])
            ->where('patient_id', (int) $data['patient_id'])
            ->where('bed_id', (int) $data['current_bed_id'])
            ->whereNull('released_at')
            ->whereHas('bed', fn (Builder $bed): Builder => $bed
                ->where('clinic_id', $clinicId)
                ->whereHas('room', fn (Builder $room): Builder => $room
                    ->where('clinic_id', $clinicId)))
            ->with(['patient', 'bed.room.department.building'])
            ->first();

        if (!$placement || !$placement->patient
            || ($placement->bed->room->department
                && (int) $placement->bed->room->department->clinic_id !== $clinicId)) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Размещение изменилось или недоступно в вашей клинике. Обновите страницу.',
            ]);
        }

        return $placement;
    }

    private function placementPayload(HospitalBedAssignment $placement): array
    {
        $bed = $placement->bed;
        $room = $bed->room;
        $department = $room->department;
        $assignedAtDisplay = '—';
        try {
            $rawAssignedAt = $placement->getAttributes()['assigned_at'] ?? null;
            if ($rawAssignedAt) {
                $assignedAtDisplay = Carbon::parse($rawAssignedAt)->format('d.m.Y H:i');
            }
        } catch (Throwable) {
            // Старая некорректная дата не мешает исправить размещение.
        }

        return [
            'assignment_id' => (int) $placement->id,
            'patient_id' => (int) $placement->patient_id,
            'current_bed_id' => (int) $bed->id,
            'department_id' => $department ? (int) $department->id : null,
            'room_id' => (int) $room->id,
            'bed_id' => (int) $bed->id,
            'patient_name' => trim(implode(' ', array_filter([
                $placement->patient->surname, $placement->patient->name, $placement->patient->patronym,
            ]))),
            'assigned_at_display' => $assignedAtDisplay,
            'location' => ($department?->name ?: 'Отделение не указано')
                . ' · Палата №' . $room->number . ' · Койка №' . $bed->number,
        ];
    }

    private function updatePlacement(Request $request, int $clinicId): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'min:1'],
            'assignment_id' => ['required', 'integer', 'min:1'],
            'current_bed_id' => ['required', 'integer', 'min:1'],
            'department_id' => ['required', 'integer', 'min:1'],
            'room_id' => ['required', 'integer', 'min:1'],
            'bed_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($clinicId, $data): void {
                // Сначала проверяем обе стороны перевода без блокировок чужих записей.
                // Принадлежность пациента диспансерному врачу не ограничивает
                // исправление его действующего размещения в собственном стационаре.
                $placement = $this->placementForEditing($clinicId, $data);
                $sourceRoomId = (int) $placement->bed->room_id;
                $sourceDepartmentId = (int) $placement->bed->room->department_id;
                $targetExists = HospitalBed::query()
                    ->whereKey((int) $data['bed_id'])
                    ->where('clinic_id', $clinicId)
                    ->where('room_id', (int) $data['room_id'])
                    ->where('enabled', true)
                    ->whereHas('room', fn (Builder $room): Builder => $room
                        ->where('clinic_id', $clinicId)
                        ->where('enabled', true)
                        ->where('department_id', (int) $data['department_id'])
                        ->whereHas('department', fn (Builder $department): Builder => $department
                            ->where('clinic_id', $clinicId)
                            ->where('enabled', true)))
                    ->exists();
                if (!$targetExists) {
                    throw ValidationException::withMessages([
                        'bed_id' => 'Выбранные отделение, палата или койка недоступны. Обновите список.',
                    ]);
                }

                // Общий порядок с оформлением и выпиской: пациент, койки,
                // палаты, отделения, конкретное размещение. Пары берём по id.
                $patient = Patient::query()
                    ->whereKey((int) $data['patient_id'])
                    ->lockForUpdate()
                    ->first();
                if (!$patient) {
                    throw ValidationException::withMessages([
                        'patient_id' => 'Пациент не найден. Обновите страницу.',
                    ]);
                }

                $beds = HospitalBed::query()
                    ->where('clinic_id', $clinicId)
                    ->whereIn('id', [(int) $data['current_bed_id'], (int) $data['bed_id']])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()->keyBy('id');
                $sourceBed = $beds->get((int) $data['current_bed_id']);
                $targetBed = $beds->get((int) $data['bed_id']);
                if (!$sourceBed || (int) $sourceBed->room_id !== $sourceRoomId) {
                    throw ValidationException::withMessages([
                        'assignment_id' => 'Текущее размещение изменилось. Обновите страницу.',
                    ]);
                }
                if (!$targetBed || !$targetBed->enabled
                    || (int) $targetBed->room_id !== (int) $data['room_id']) {
                    throw ValidationException::withMessages([
                        'bed_id' => 'Выбранная койка изменилась или недоступна. Обновите список.',
                    ]);
                }

                $rooms = HospitalRoom::query()
                    ->where('clinic_id', $clinicId)
                    ->whereIn('id', [$sourceRoomId, (int) $data['room_id']])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()->keyBy('id');
                $sourceRoom = $rooms->get($sourceRoomId);
                $targetRoom = $rooms->get((int) $data['room_id']);
                if (!$sourceRoom || (int) $sourceRoom->department_id !== $sourceDepartmentId) {
                    throw ValidationException::withMessages([
                        'assignment_id' => 'Текущее размещение изменилось. Обновите страницу.',
                    ]);
                }
                if (!$targetRoom || !$targetRoom->enabled
                    || (int) $targetRoom->department_id !== (int) $data['department_id']) {
                    throw ValidationException::withMessages([
                        'room_id' => 'Выбранная палата изменилась или недоступна. Обновите список.',
                    ]);
                }

                $departments = HospitalDepartment::query()
                    ->where('clinic_id', $clinicId)
                    ->whereIn('id', [$sourceDepartmentId, (int) $data['department_id']])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()->keyBy('id');
                $targetDepartment = $departments->get((int) $data['department_id']);
                if (!$targetDepartment || !$targetDepartment->enabled
                    || ($sourceDepartmentId > 0 && !$departments->has($sourceDepartmentId))) {
                    throw ValidationException::withMessages([
                        'department_id' => 'Отделение изменилось или недоступно. Обновите список.',
                    ]);
                }

                $assignment = HospitalBedAssignment::query()
                    ->whereKey((int) $data['assignment_id'])
                    ->lockForUpdate()
                    ->first();
                if (!$assignment || $assignment->released_at !== null
                    || (int) $assignment->patient_id !== (int) $patient->id
                    || (int) $assignment->bed_id !== (int) $data['current_bed_id']) {
                    throw ValidationException::withMessages([
                        'assignment_id' => 'Размещение уже изменено или завершено. Обновите страницу.',
                    ]);
                }
                if (HospitalBedAssignment::query()
                    ->where('patient_id', $patient->id)
                    ->whereNull('released_at')
                    ->whereKeyNot($assignment->id)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'assignment_id' => 'У пациента несколько активных размещений. Проверьте их перед редактированием.',
                    ]);
                }
                if (HospitalBedAssignment::query()
                    ->where('bed_id', $targetBed->id)
                    ->whereNull('released_at')
                    ->whereKeyNot($assignment->id)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'bed_id' => 'Койка уже занята. Выберите другую.',
                    ]);
                }

                $this->validateDepartmentPatient($targetDepartment, $patient);

                // Редактируем существующую запись: пациент, дата поступления,
                // комментарий и состояние госпитализации остаются прежними.
                if ((int) $assignment->bed_id !== (int) $targetBed->id) {
                    $assignment->bed_id = $targetBed->id;
                    $assignment->save();
                }
            }, 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23505') {
                throw ValidationException::withMessages([
                    'bed_id' => 'Койка занята или размещение изменилось. Обновите список.',
                ]);
            }
            throw $exception;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Запись размещения обновлена.',
        ]);
    }

    private function inpatientClinic(Request $request, bool $doctorOnly = false): Clinic
    {
        $user = $request->user();
        $doctor = $user && method_exists($user, 'doctor') ? $user->doctor()->first() : null;
        $nurse = !$doctorOnly && $user && method_exists($user, 'nurse') ? $user->nurse()->first() : null;
        $clinicId = (int) ($doctor?->clinic_id ?? $nurse?->clinic_id ?? 0);

        abort_unless($clinicId > 0, 403);
        $clinic = Clinic::findOrFail($clinicId);
        abort_unless($clinic->clinic_type === Clinic::TYPE_INPATIENT, 404);

        return $clinic;
    }

    private function scopedPatient(int $clinicId, int $patientId, bool $lockForUpdate = true): Patient
    {
        $query = $this->scopePatientsToClinic(
            Patient::query()->whereKey($patientId),
            $clinicId
        );
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $patient = $query->first();

        if (!$patient) {
            throw ValidationException::withMessages([
                'patient_id' => 'Пациент не найден среди пациентов вашей клиники.',
            ]);
        }
        return $patient;
    }

    private function scopePatientsToClinic(Builder $query, int $clinicId): Builder
    {
        $hasClinicId = Schema::hasColumn('patients', 'clinic_id');
        $hasDoctorId = Schema::hasColumn('patients', 'doctor_id');
        $hasRegistry = Schema::hasTable('registry_records')
            && Schema::hasColumn('registry_records', 'clinic_id')
            && Schema::hasColumn('registry_records', 'patient_id');

        if (!$hasClinicId && !$hasDoctorId && !$hasRegistry) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $scope) use ($clinicId, $hasClinicId, $hasDoctorId, $hasRegistry): void {
            if ($hasClinicId) {
                $scope->where('clinic_id', $clinicId);
            }
            if ($hasDoctorId) {
                $scope->orWhereIn('doctor_id', DB::table('doctors')
                    ->select('id')->where('clinic_id', $clinicId));
            }
            if ($hasRegistry) {
                $scope->orWhereIn('id', DB::table('registry_records')
                    ->select('patient_id')->where('clinic_id', $clinicId));
            }
        });
    }

    private function validateDepartmentPatient(HospitalDepartment $department, Patient $patient): void
    {
        // Возраст и пол проверяются и для первичной госпитализации,
        // и для перевода на другую койку / в другое отделение.
        HospitalDepartmentAdmissionPolicy::assertEligible($patient, $department);
    }
}
