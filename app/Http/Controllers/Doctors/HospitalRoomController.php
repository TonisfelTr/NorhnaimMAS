<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\HospitalBed;
use App\Models\HospitalBedAssignment;
use App\Models\HospitalDepartment;
use App\Models\HospitalRoom;
use App\Models\HospitalRoomItem;
use App\Models\Patient;
use App\Http\Middleware\HospitalRoomContextMiddleware;
use App\Support\HospitalBedRelease;
use App\Support\HospitalDepartmentAdmissionPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class HospitalRoomController extends Controller
{
    public function index()
    {
        [$doctor, $clinic] = $this->currentInpatientClinic();
        $temporary = $this->isTemporaryHospitalContext();

        // Временные настройки получают СОБСТВЕННЫЕ URL и layout без navbar
        // амбулатории. Оригинальный Vue-конструктор остаётся тем же самым.
        $prefix = $temporary
            ? 'doctors.hospital-impersonation.rooms.'
            : 'doctors.settings.rooms.';
        $params = $temporary ? ['hospital' => (int) $clinic->id] : [];

        return view(
            $temporary ? 'doctors.settings.hospital-rooms-temporary' : 'doctors.settings.rooms',
            [
                'settingsDoctor' => $doctor,
                'settingsIsPrivatePractice' => false,
                'settingsIsInpatient' => true,
                'settingsContextLabel' => 'Стационар',
                'settingsContextIcon' => 'bi-hospital',
                'roomBootstrapUrl' => route($prefix . 'data', $params),
                'roomStoreUrl' => route($prefix . 'store', $params),
                'roomUpdateUrlTemplate' => route($prefix . 'update', $params + ['room' => '__ROOM__']),
                'roomDeleteUrlTemplate' => route($prefix . 'delete', $params + ['room' => '__ROOM__']),
                'roomLayoutUrlTemplate' => route($prefix . 'layout', $params + ['room' => '__ROOM__']),
                // Отдельных API для пациентов во временном режиме нет.
                // Vue уже поддерживает patientPlacementEnabled=false.
                'bedAssignUrlTemplate' => $temporary ? '#' : route($prefix . 'beds.assign', ['bed' => '__BED__']),
                'bedReleaseUrlTemplate' => $temporary ? '#' : route($prefix . 'beds.release', ['bed' => '__BED__']),
                'patientSearchUrl' => $temporary ? '#' : route($prefix . 'patients.search'),
                'patientCardUrlTemplate' => $temporary ? '#' : route('doctors.patients.medical_card', ['patient' => '__PATIENT__']),
                'clinicName' => (string) $clinic->name,
                'patientPlacementEnabled' => false,
                'temporaryHospitalMode' => $temporary,
            ]
        );
    }

    public function data(Request $request): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $departmentId = max(0, (int) $request->input('department_id', 0));
        $perPage = min(20, max(4, (int) $request->input('per_page', 8)));
        $page = max(1, (int) $request->input('page', 1));

        $departments = HospitalDepartment::query()
            ->with('building')
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('number')
            ->orderBy('name')
            ->get();

        $roomsQuery = HospitalRoom::query()
            ->with([
                'department.building',
                'items',
                $this->isTemporaryHospitalContext()
                    ? 'beds.currentAssignment'
                    : 'beds.currentAssignment.patient',
            ])
            ->where('clinic_id', $clinicId)
            ->when(
                $departmentId > 0,
                fn (Builder $query): Builder => $query->where(
                    'department_id',
                    $departmentId
                )
            )
            ->orderBy('sort_order')
            ->orderBy('number')
            ->orderBy('id');

        $rooms = $roomsQuery->paginate(
            $perPage,
            ['*'],
            'page',
            $page
        );

        /*
         * Если после удаления последней палаты текущая страница стала пустой,
         * автоматически возвращаем пользователя на последнюю существующую.
         */
        if ($page > 1 && $page > $rooms->lastPage()) {
            $page = max(1, $rooms->lastPage());
            $rooms = $roomsQuery->paginate(
                $perPage,
                ['*'],
                'page',
                $page
            );
        }

        /*
         * Статистика сверху остаётся общей по всему стационару. Для неё больше
         * не нужно загружать все палаты, все койки и пациентов в память PHP.
         */
        $bedStatsQuery = HospitalBed::query()
            ->whereHas(
                'room',
                fn (Builder $query): Builder => $query->where(
                    'clinic_id',
                    $clinicId
                )
            );

        return response()->json([
            'departments' => $departments
                ->map(function (HospitalDepartment $department): array {
                    $age = HospitalDepartmentAdmissionPolicy::restrictions($department);

                    return [
                        'id' => (int) $department->id,
                        'name' => (string) $department->name,
                        'number' => (string) ($department->number ?? ''),
                        'sex_type' => (string) ($department->sex_type ?? 'mixed'),
                        'profile' => (string) ($department->profile ?? ''),
                        'profile_category' => HospitalDepartmentAdmissionPolicy::profileCategory($department),
                        'is_pediatric' => ($age['group'] ?? null) === 'children',
                        'is_age_restricted' => $age !== null,
                        'age_group' => $age['group'] ?? null,
                        'age_group_label' => $age['label'] ?? null,
                        'age_min_inclusive' => $age['min_age_inclusive'] ?? null,
                        'age_limit_exclusive' => $age['max_age_exclusive'] ?? null,
                        'building' => $department->building
                            ? [
                                'id' => (int) $department->building->id,
                                'label' => $this->buildingLabel($department),
                                'address' => (string) ($department->building->address ?? ''),
                            ]
                            : null,
                    ];
                })
                ->values(),
            'rooms' => $rooms->getCollection()
                ->map(fn (HospitalRoom $room): array => $this->roomPayload(
                    $room,
                    $this->isTemporaryHospitalContext()
                ))
                ->values(),
            'pagination' => [
                'current_page' => (int) $rooms->currentPage(),
                'last_page' => (int) $rooms->lastPage(),
                'per_page' => (int) $rooms->perPage(),
                'total' => (int) $rooms->total(),
                'from' => (int) ($rooms->firstItem() ?? 0),
                'to' => (int) ($rooms->lastItem() ?? 0),
            ],
            'stats' => [
                'rooms' => HospitalRoom::query()
                    ->where('clinic_id', $clinicId)
                    ->count(),
                'beds' => (clone $bedStatsQuery)->count(),
                'occupied' => (clone $bedStatsQuery)
                    ->whereHas('currentAssignment')
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $validated = $this->validateRoom($request, $clinicId);

        $room = HospitalRoom::create([
            'clinic_id' => $clinicId,
            'department_id' => (int) $validated['department_id'],
            'number' => trim((string) $validated['number']),
            'name' => $this->nullableTrim($validated['name'] ?? null),
            'floor' => $this->nullableTrim($validated['floor'] ?? null),
            'canvas_width' => (int) ($validated['canvas_width'] ?? 900),
            'canvas_height' => (int) ($validated['canvas_height'] ?? 560),
            'enabled' => true,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Палата создана.',
            'room_id' => (int) $room->id,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        // В пути временного режима сначала идёт {hospital}, затем {room}.
        // Получаем ID палаты по ИМЕНИ параметра, не по позиции аргумента.
        $room = (int) $request->route('room');
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $model = $this->roomForClinic($room, $clinicId);

        $validated = $this->validateRoom(
            $request,
            $clinicId,
            (int) $model->id
        );

        $temporaryHospital = $this->isTemporaryHospitalContext();

        DB::transaction(function () use ($request, $room, $clinicId, $validated, $temporaryHospital): void {
            $lockedRoom = HospitalRoom::query()
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->findOrFail($room);
            if ($temporaryHospital
                && (int) $lockedRoom->department_id !== (int) $validated['department_id']
                && $lockedRoom->beds()->whereHas('currentAssignment')->exists()) {
                throw ValidationException::withMessages([
                    'department_id' => 'Нельзя менять отделение палаты, в которой размещены пациенты.',
                ]);
            }
            $department = HospitalDepartment::query()
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->findOrFail((int) $validated['department_id']);

            // Переназначение самой палаты также переносит все занятые койки.
            // Старые несоответствия не мешают исправлять номер/схему той же палаты.
            if ((int) $lockedRoom->department_id !== (int) $department->id) {
                $assignments = HospitalBedAssignment::query()
                    ->whereNull('released_at')
                    ->whereIn('bed_id', HospitalBed::query()
                        ->where('room_id', $lockedRoom->id)
                        ->select('id'))
                    ->with('patient')
                    ->get();

                foreach ($assignments as $assignment) {
                    $violation = $assignment->patient
                        ? HospitalDepartmentAdmissionPolicy::violation($assignment->patient, $department)
                        : 'В одном из размещений отсутствуют данные пациента.';
                    if ($violation !== null) {
                        throw ValidationException::withMessages([
                            'department_id' => 'Нельзя перенести занятую палату в выбранное отделение. ' . $violation,
                        ]);
                    }
                }
            }

            $lockedRoom->fill([
                'department_id' => (int) $validated['department_id'],
                'number' => trim((string) $validated['number']),
                'name' => $this->nullableTrim($validated['name'] ?? null),
                'floor' => $this->nullableTrim($validated['floor'] ?? null),
                'canvas_width' => (int) ($validated['canvas_width'] ?? $lockedRoom->canvas_width),
                'canvas_height' => (int) ($validated['canvas_height'] ?? $lockedRoom->canvas_height),
                'enabled' => $request->boolean('enabled', true),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
            ])->save();
        }, 3);

        return response()->json([
            'ok' => true,
            'message' => 'Палата обновлена.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $room = (int) $request->route('room');
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $model = $this->roomForClinic($room, $clinicId);

        $occupied = $model->beds()
            ->whereHas('currentAssignment')
            ->exists();

        if ($occupied) {
            throw ValidationException::withMessages([
                'room' => 'Нельзя удалить палату, пока в ней есть занятые койки.',
            ]);
        }

        $model->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Палата удалена.',
        ]);
    }

    public function saveLayout(Request $request): JsonResponse
    {
        $room = (int) $request->route('room');
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $model = $this->roomForClinic($room, $clinicId);

        $validated = $request->validate([
            'canvas_width' => ['required', 'integer', 'min:500', 'max:1600'],
            'canvas_height' => ['required', 'integer', 'min:350', 'max:1200'],

            'beds' => ['array', 'max:100'],
            'beds.*.id' => ['nullable', 'integer'],
            'beds.*.number' => ['required', 'string', 'max:50'],
            'beds.*.label' => ['nullable', 'string', 'max:255'],
            'beds.*.x' => ['required', 'integer', 'min:0', 'max:2000'],
            'beds.*.y' => ['required', 'integer', 'min:0', 'max:2000'],
            'beds.*.width' => ['required', 'integer', 'min:40', 'max:600'],
            'beds.*.height' => ['required', 'integer', 'min:40', 'max:600'],
            'beds.*.rotation' => ['required', 'numeric', 'min:0', 'max:359.99'],
            'beds.*.enabled' => ['required', 'boolean'],

            'items' => ['array', 'max:200'],
            'items.*.type' => [
                'required',
                Rule::in(HospitalRoomItem::TYPES),
            ],
            'items.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.x' => ['required', 'integer', 'min:-600', 'max:2200'],
            'items.*.y' => ['required', 'integer', 'min:-600', 'max:2200'],
            'items.*.width' => ['required', 'integer', 'min:30', 'max:600'],
            'items.*.height' => ['required', 'integer', 'min:30', 'max:600'],
            'items.*.rotation' => ['required', 'numeric', 'min:0', 'max:359.99'],
        ]);

        $beds = collect($validated['beds'] ?? [])->map(function (array $bed): array {
            $bed['number'] = trim((string) $bed['number']);
            return $bed;
        });

        if ($beds->pluck('number')->filter()->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'beds' => 'Номера коек внутри одной палаты не должны повторяться.',
            ]);
        }

        DB::transaction(function () use ($model, $clinicId, $beds, $validated): void {
            $model->canvas_width = (int) $validated['canvas_width'];
            $model->canvas_height = (int) $validated['canvas_height'];
            $model->save();

            $existing = $model->beds()
                ->with('currentAssignment')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $incomingIds = $beds
                ->pluck('id')
                ->filter()
                ->map(fn ($id): int => (int) $id)
                ->values();

            $toDelete = $existing
                ->reject(fn (HospitalBed $bed): bool => $incomingIds->contains((int) $bed->id));

            $occupiedToDelete = $toDelete
                ->first(fn (HospitalBed $bed): bool => (bool) $bed->currentAssignment);

            if ($occupiedToDelete) {
                throw ValidationException::withMessages([
                    'beds' => 'Нельзя удалить занятую койку №' . $occupiedToDelete->number . '.',
                ]);
            }

            $toDelete->each(fn (HospitalBed $bed) => $bed->delete());

            foreach ($beds as $bedData) {
                $bedId = !empty($bedData['id']) ? (int) $bedData['id'] : null;

                if ($bedId) {
                    $bed = $existing->get($bedId);
                    abort_unless($bed, 422, 'Койка не относится к выбранной палате.');
                } else {
                    $bed = new HospitalBed();
                    $bed->clinic_id = $clinicId;
                    $bed->room_id = $model->id;
                }

                $bed->number = $bedData['number'];
                $bed->label = $this->nullableTrim($bedData['label'] ?? null);
                $bed->x = (int) $bedData['x'];
                $bed->y = (int) $bedData['y'];
                $bed->width = (int) $bedData['width'];
                $bed->height = (int) $bedData['height'];
                $bed->rotation = round((float) $bedData['rotation'], 2);
                $bed->enabled = (bool) $bedData['enabled'];
                $bed->save();
            }

            $model->items()->delete();

            foreach ($validated['items'] ?? [] as $index => $itemData) {
                $model->items()->create([
                    'type' => $itemData['type'],
                    'label' => $this->nullableTrim($itemData['label'] ?? null),
                    'x' => (int) $itemData['x'],
                    'y' => (int) $itemData['y'],
                    'width' => (int) $itemData['width'],
                    'height' => (int) $itemData['height'],
                    'rotation' => round((float) $itemData['rotation'], 2),
                    'sort_order' => $index,
                ]);
            }
        }, 3);

        return response()->json([
            'ok' => true,
            'message' => 'Карта палаты сохранена.',
        ]);
    }

    public function patientSearch(Request $request): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $q = trim((string) $request->query('q', ''));
        $bedId = max(0, (int) $request->query('bed_id', 0));

        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        $department = null;

        if ($bedId > 0) {
            $bed = HospitalBed::query()
                ->with('room.department')
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->whereHas('room', fn (Builder $room): Builder => $room
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->whereHas('department', fn (Builder $department): Builder => $department
                        ->where('clinic_id', $clinicId)
                        ->where('enabled', true)))
                ->findOrFail($bedId);

            $department = $bed->room?->department;
        }

        $search = '%' . $q . '%';
        $query = $this->patientQuery($clinicId)
            ->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('surname', 'ilike', $search)
                    ->orWhere('name', 'ilike', $search)
                    ->orWhere('patronym', 'ilike', $search)
                    ->orWhereRaw(
                        "CONCAT_WS(' ', surname, name, patronym) ILIKE ?",
                        [$search]
                    );
            })
            ->whereNotIn(
                'id',
                HospitalBedAssignment::query()
                    ->whereNotNull('patient_id')
                    ->whereNull('released_at')
                    ->select('patient_id')
            );

        $patients = HospitalDepartmentAdmissionPolicy::applyToPatients($query, $department)
            ->orderBy('surname')
            ->orderBy('name')
            ->limit(20)
            ->get();
        $age = HospitalDepartmentAdmissionPolicy::restrictions($department);

        return response()->json([
            'items' => $patients->map(
                fn (Patient $patient): array => $this->patientPayload($patient)
            )->values(),
            'is_pediatric' => ($age['group'] ?? null) === 'children',
            'is_age_restricted' => $age !== null,
            'age_group' => $age['group'] ?? null,
            'age_group_label' => $age['label'] ?? null,
            'age_min_inclusive' => $age['min_age_inclusive'] ?? null,
            'age_limit_exclusive' => $age['max_age_exclusive'] ?? null,
        ]);
    }

    public function assignBed(Request $request, int $bed): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $patientId = (int) $validated['patient_id'];

        DB::transaction(function () use (
            $bed,
            $clinicId,
            $patientId,
            $validated
        ): void {
            // Единый порядок с оформлением и выпиской: пациент, затем койка.
            $patient = $this->patientQuery($clinicId)
                ->whereKey($patientId)
                ->lockForUpdate()
                ->firstOrFail();

            $bedModel = HospitalBed::query()
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->findOrFail($bed);

            if (!$bedModel->enabled) {
                throw ValidationException::withMessages([
                    'bed' => 'Эта койка отключена.',
                ]);
            }

            $room = HospitalRoom::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->lockForUpdate()
                ->find($bedModel->room_id);
            $department = $room
                ? HospitalDepartment::query()
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true)
                    ->lockForUpdate()
                    ->find($room->department_id)
                : null;
            if (!$room || !$department) {
                throw ValidationException::withMessages([
                    'bed' => 'Палата или отделение этой койки не найдены либо недоступны.',
                ]);
            }

            if (
                HospitalBedAssignment::query()
                    ->where('bed_id', $bedModel->id)
                    ->whereNull('released_at')
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'bed' => 'Койка уже занята.',
                ]);
            }

            HospitalDepartmentAdmissionPolicy::assertEligible($patient, $department, 'patient_id');

            if (
                HospitalBedAssignment::query()
                    ->where('patient_id', $patient->id)
                    ->whereNull('released_at')
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'patient_id' => 'Пациент уже размещён на другой койке.',
                ]);
            }

            HospitalBedAssignment::create([
                'bed_id' => $bedModel->id,
                'patient_id' => $patient->id,
                'assigned_at' => now(),
                'comment' => $this->nullableTrim($validated['comment'] ?? null),
            ]);
        }, 3);

        return response()->json([
            'ok' => true,
            'message' => 'Пациент размещён на койке.',
        ]);
    }

    public function releaseBed(Request $request, int $bed): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $validated = $request->validate([
            'intent' => ['sometimes', 'string', Rule::in(['release', 'discharge'])],
            'patient_id' => ['required_if:intent,discharge', 'required_with:assignment_id', 'nullable', 'integer', 'min:1'],
            'assignment_id' => ['required_if:intent,discharge', 'required_with:patient_id', 'nullable', 'integer', 'min:1'],
        ]);
        $isDischarge = ($validated['intent'] ?? 'release') === 'discharge';

        HospitalBedRelease::release(
            $clinicId,
            $bed,
            isset($validated['patient_id']) ? (int) $validated['patient_id'] : null,
            isset($validated['assignment_id']) ? (int) $validated['assignment_id'] : null,
            $isDischarge
        );

        return response()->json([
            'ok' => true,
            'message' => $isDischarge ? 'Пациент выписан из стационара.' : 'Койка освобождена.',
        ]);
    }

    private function isTemporaryHospitalContext(): bool
    {
        return request()->routeIs('doctors.hospital-impersonation.rooms.*');
    }

    private function currentInpatientClinic(): array
    {
        $doctor = auth()->user()?->doctor;
        abort_unless($doctor, 403);

        if ($this->isTemporaryHospitalContext()) {
            // Отдельный route-middleware уже проверил session, actor, роль,
            // parent_clinic_id и выбранный hospital. Никаких подмен doctor.clinic_id.
            $hospital = request()->attributes->get(HospitalRoomContextMiddleware::HOSPITAL_ATTRIBUTE);
            abort_unless($hospital instanceof Clinic
                && (string) $hospital->clinic_type === Clinic::TYPE_INPATIENT, 403);
            return [$doctor, $hospital];
        }

        abort_unless((int) $doctor->clinic_id > 0, 403);
        $clinic = Clinic::findOrFail((int) $doctor->clinic_id);
        abort_unless((string) $clinic->clinic_type === Clinic::TYPE_INPATIENT, 404);

        return [$doctor, $clinic];
    }

    private function roomForClinic(int $roomId, int $clinicId): HospitalRoom
    {
        return HospitalRoom::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail($roomId);
    }

    private function validateRoom(
        Request $request,
        int $clinicId,
        ?int $roomId = null
    ): array {
        $departmentId = (int) $request->input('department_id');

        $numberRule = Rule::unique('hospital_rooms', 'number')
            ->where('department_id', $departmentId);

        if ($roomId) {
            $numberRule->ignore($roomId);
        }

        return $request->validate([
            'department_id' => [
                'required',
                'integer',
                Rule::exists('hospital_departments', 'id')
                    ->where('clinic_id', $clinicId),
            ],
            'number' => [
                'required',
                'string',
                'max:50',
                $numberRule,
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'canvas_width' => ['nullable', 'integer', 'min:500', 'max:1600'],
            'canvas_height' => ['nullable', 'integer', 'min:350', 'max:1200'],
            'enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);
    }

    private function roomPayload(HospitalRoom $room, bool $maskPatientDetails = false): array
    {
        $age = HospitalDepartmentAdmissionPolicy::restrictions($room->department);
        $beds = $room->beds->map(function (HospitalBed $bed) use ($maskPatientDetails): array {
            $assignment = $bed->currentAssignment;
            $patient = $maskPatientDetails ? null : $assignment?->patient;

            return [
                'id' => (int) $bed->id,
                'number' => (string) $bed->number,
                'label' => (string) ($bed->label ?? ''),
                'x' => (int) $bed->x,
                'y' => (int) $bed->y,
                'width' => (int) $bed->width,
                'height' => (int) $bed->height,
                'rotation' => round((float) $bed->rotation, 2),
                'enabled' => (bool) $bed->enabled,
                'occupied' => (bool) $assignment,
                'patient' => $patient
                    ? $this->patientPayload($patient)
                    : null,
                'assigned_at' => !$maskPatientDetails && $assignment && $assignment->assigned_at
                    ? Carbon::parse($assignment->assigned_at)->format('d.m.Y H:i')
                    : null,
            ];
        })->values();

        return [
            'id' => (int) $room->id,
            'department_id' => (int) $room->department_id,
            'department_name' => (string) ($room->department?->name ?? ''),
            'department_number' => (string) ($room->department?->number ?? ''),
            'department_sex_type' => (string) ($room->department?->sex_type ?? 'mixed'),
            'department_is_pediatric' => ($age['group'] ?? null) === 'children',
            'department_is_age_restricted' => $age !== null,
            'department_age_group' => $age['group'] ?? null,
            'department_age_group_label' => $age['label'] ?? null,
            'department_age_min_inclusive' => $age['min_age_inclusive'] ?? null,
            'department_age_limit_exclusive' => $age['max_age_exclusive'] ?? null,
            'building_label' => $room->department
                ? $this->buildingLabel($room->department)
                : '',
            'number' => (string) $room->number,
            'name' => (string) ($room->name ?? ''),
            'floor' => (string) ($room->floor ?? ''),
            'canvas_width' => (int) $room->canvas_width,
            'canvas_height' => (int) $room->canvas_height,
            'enabled' => (bool) $room->enabled,
            'sort_order' => (int) $room->sort_order,
            'beds' => $beds,
            'items' => $room->items->map(fn (HospitalRoomItem $item): array => [
                'id' => (int) $item->id,
                'type' => (string) $item->type,
                'label' => (string) ($item->label ?? ''),
                'x' => (int) $item->x,
                'y' => (int) $item->y,
                'width' => (int) $item->width,
                'height' => (int) $item->height,
                'rotation' => round((float) $item->rotation, 2),
            ])->values(),
            'stats' => [
                'beds' => $beds->count(),
                'occupied' => $beds->where('occupied', true)->count(),
                'free' => $beds->where('occupied', false)->count(),
            ],
        ];
    }

    private function patientPayload(Patient $patient): array
    {
        $birthAt = null;
        $rawBirthAt = $patient->getAttributes()['birth_at'] ?? null;

        if ($rawBirthAt !== null && $rawBirthAt !== '') {
            try {
                $birthAt = Carbon::parse($rawBirthAt)->format('d.m.Y');
            } catch (Throwable) {
                $birthAt = (string) $rawBirthAt;
            }
        }

        return [
            'id' => (int) $patient->id,
            'name' => trim(implode(' ', array_filter([
                $patient->surname,
                $patient->name,
                $patient->patronym,
            ]))),
            'birth_at' => $birthAt,
            'medical_card_url' => route(
                'doctors.patients.medical_card',
                ['patient' => $patient->id]
            ),
        ];
    }

    private function patientQuery(int $clinicId): Builder
    {
        $query = Patient::query();

        /*
         * Пациенты в проекте исторически привязаны к клинике не одним
         * способом. У части записей заполнен patients.clinic_id, у части —
         * только doctor_id, а часть попадает в клинику через registry_records.
         * Поэтому фильтр только по patients.clinic_id отбрасывал реальных
         * пациентов и поиск возле койки всегда возвращал пустой список.
         */
        $hasClinicId = Schema::hasColumn('patients', 'clinic_id');
        $hasDoctorId = Schema::hasColumn('patients', 'doctor_id');
        $hasRegistryScope =
            Schema::hasTable('registry_records')
            && Schema::hasColumn('registry_records', 'clinic_id')
            && Schema::hasColumn('registry_records', 'patient_id');

        /*
         * Даже в APP_DEBUG нельзя снимать ограничение по клинике.
         * Иначе в поиске размещения появляются все тестовые записи из
         * patients, в том числе сиды других клиник, что визуально выглядит
         * как совершенно посторонние сотрудники/пациенты.
         *
         * Размещение в стационаре всегда работает только в контексте
         * текущей клиники.
         */
        if (!$hasClinicId && !$hasDoctorId && !$hasRegistryScope) {
            return $query->whereRaw('1 = 0');
        }

        $query->where(
            function (Builder $patientQuery) use (
                $clinicId,
                $hasClinicId,
                $hasDoctorId,
                $hasRegistryScope
            ): void {
                $hasCondition = false;

                if ($hasClinicId) {
                    $patientQuery->where('clinic_id', $clinicId);
                    $hasCondition = true;
                }

                if ($hasDoctorId) {
                    $method = $hasCondition ? 'orWhereIn' : 'whereIn';
                    $patientQuery->{$method}(
                        'doctor_id',
                        DB::table('doctors')
                            ->select('id')
                            ->where('clinic_id', $clinicId)
                    );
                    $hasCondition = true;
                }

                if ($hasRegistryScope) {
                    $method = $hasCondition ? 'orWhereIn' : 'whereIn';
                    $patientQuery->{$method}(
                        'id',
                        DB::table('registry_records')
                            ->select('patient_id')
                            ->where('clinic_id', $clinicId)
                    );
                }
            }
        );

        return $query;
    }

    private function buildingLabel(HospitalDepartment $department): string
    {
        $building = $department->building;

        if (!$building) {
            return 'Корпус не указан';
        }

        return trim(
            'Корпус №' . $building->number
            . ($building->name ? ' — ' . $building->name : '')
        );
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value !== '' ? $value : null;
    }
}
