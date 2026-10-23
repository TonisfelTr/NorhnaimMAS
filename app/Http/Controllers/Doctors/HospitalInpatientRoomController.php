<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\HospitalBed;
use App\Models\HospitalDepartment;
use App\Models\HospitalRoom;
use App\Support\HospitalDepartmentEligibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Throwable;

class HospitalInpatientRoomController extends Controller
{
    private const ROOMS_PER_PAGE = 8;

    public function index(Request $request): View
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;
        $buildingId = max(0, $request->integer('building_id'));
        $departmentId = max(0, $request->integer('department_id'));
        $selectedRoomId = max(0, $request->integer('room_id'));

        $allDepartments = HospitalDepartment::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->with('building')
            ->orderBy('sort_order')
            ->orderBy('number')
            ->orderBy('name')
            ->get();

        $buildings = $allDepartments
            ->pluck('building')
            ->filter()
            ->unique('id')
            ->sortBy(function ($building): string {
                return str_pad((string) ($building->number ?? ''), 10, '0', STR_PAD_LEFT)
                    . '|' . mb_strtolower((string) ($building->name ?? ''));
            })
            ->values();

        $departments = $allDepartments
            ->when(
                $buildingId > 0,
                static fn ($items) => $items->where('building_id', $buildingId)
            )
            ->values();

        if (
            $departmentId > 0
            && !$departments->contains(
                static fn ($department): bool =>
                    (int) $department->id === $departmentId
            )
        ) {
            $departmentId = 0;
        }

        $roomListQuery = HospitalRoom::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->when(
                $buildingId > 0,
                static fn (Builder $query): Builder =>
                    $query->whereHas(
                        'department',
                        static fn (Builder $departmentQuery): Builder =>
                            $departmentQuery->where('building_id', $buildingId)
                    )
            )
            ->when(
                $departmentId > 0,
                static fn (Builder $query): Builder =>
                    $query->where('department_id', $departmentId)
            )
            ->with('department.building')
            ->withCount('beds')
            ->withCount([
                'beds as occupied_beds_count' =>
                    static fn (Builder $query): Builder =>
                        $query->whereHas('currentAssignment'),
            ])
            ->orderBy('sort_order')
            ->orderBy('number');

        $rooms = $roomListQuery
            ->paginate(self::ROOMS_PER_PAGE, ['*'], 'rooms_page');

        $rooms->appends(array_filter([
            'building_id' => $buildingId ?: null,
            'department_id' => $departmentId ?: null,
        ]));

        if ($selectedRoomId === 0 && $rooms->count() > 0) {
            $selectedRoomId = (int) $rooms->first()->id;
        }

        $selectedRoom = null;

        if ($selectedRoomId > 0) {
            $selectedRoom = HospitalRoom::query()
                ->with([
                    'department.building',
                    'items',
                    'beds.currentAssignment.patient',
                ])
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->when(
                    $buildingId > 0,
                    static fn (Builder $query): Builder =>
                        $query->whereHas(
                            'department',
                            static fn (Builder $departmentQuery): Builder =>
                                $departmentQuery->where('building_id', $buildingId)
                        )
                )
                ->when(
                    $departmentId > 0,
                    static fn (Builder $query): Builder =>
                        $query->where('department_id', $departmentId)
                )
                ->find($selectedRoomId);
        }

        if (!$selectedRoom && $rooms->count() > 0) {
            $selectedRoomId = (int) $rooms->first()->id;

            $selectedRoom = HospitalRoom::query()
                ->with([
                    'department.building',
                    'items',
                    'beds.currentAssignment.patient',
                ])
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->when(
                    $buildingId > 0,
                    static fn (Builder $query): Builder =>
                        $query->whereHas(
                            'department',
                            static fn (Builder $departmentQuery): Builder =>
                                $departmentQuery->where('building_id', $buildingId)
                        )
                )
                ->when(
                    $departmentId > 0,
                    static fn (Builder $query): Builder =>
                        $query->where('department_id', $departmentId)
                )
                ->find($selectedRoomId);
        }

        $statsQuery = HospitalRoom::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true);

        $roomsCount = (clone $statsQuery)->count();
        $bedsCount = HospitalBed::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->count();
        $occupiedBedsCount = HospitalBed::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->whereHas('currentAssignment')
            ->count();

        return view('doctors.inpatient.rooms', [
            'clinic' => $clinic,
            'buildings' => $buildings,
            'buildingId' => $buildingId,
            'departments' => $departments,
            'departmentId' => $departmentId,
            'rooms' => $rooms,
            'selectedRoom' => $selectedRoom
                ? $this->roomPayload($selectedRoom)
                : null,
            'roomsCount' => $roomsCount,
            'bedsCount' => $bedsCount,
            'occupiedBedsCount' => $occupiedBedsCount,
            'freeBedsCount' => max(0, $bedsCount - $occupiedBedsCount),
        ]);
    }

    private function roomPayload(HospitalRoom $room): array
    {
        $canvasWidth = max(1, (int) $room->canvas_width);
        $canvasHeight = max(1, (int) $room->canvas_height);

        $legacyBedCoordinates = $room->beds->contains(
            static fn (HospitalBed $bed): bool =>
                (float) $bed->x < 0 || (float) $bed->y < 0
        );

        $beds = $room->beds
            ->sortBy('number', SORT_NATURAL)
            ->map(function (HospitalBed $bed) use (
                $canvasWidth,
                $canvasHeight,
                $legacyBedCoordinates
            ): array {
                $assignment = $bed->currentAssignment;
                $patient = $assignment?->patient;
                $assignedAt = $assignment?->assigned_at
                    ? Carbon::parse($assignment->assigned_at)
                    : null;
                $stayDays = $assignedAt
                    ? max(
                        1,
                        $assignedAt
                            ->copy()
                            ->startOfDay()
                            ->diffInDays(now()->startOfDay()) + 1
                    )
                    : null;

                [$displayX, $displayY] = $this->bedRuntimePosition(
                    $bed,
                    $legacyBedCoordinates
                );

                return [
                    'id' => (int) $bed->id,
                    'number' => (string) $bed->number,
                    'label' => (string) ($bed->label ?? ''),
                    'x' => $displayX,
                    'y' => $displayY,
                    'width' => (float) $bed->width,
                    'height' => (float) $bed->height,
                    'rotation' => (float) $bed->rotation,
                    'style' => $this->objectStyle(
                        $displayX,
                        $displayY,
                        (float) $bed->width,
                        (float) $bed->height,
                        (float) $bed->rotation,
                        $canvasWidth,
                        $canvasHeight
                    ),
                    'occupied' => (bool) $assignment,
                    'patient' => $patient
                        ? [
                            'id' => (int) $patient->id,
                            'name' => trim(implode(' ', array_filter([
                                $patient->surname,
                                $patient->name,
                                $patient->patronym,
                            ]))),
                            'short_name' => $this->shortName(
                                (string) ($patient->surname ?? ''),
                                (string) ($patient->name ?? ''),
                                (string) ($patient->patronym ?? '')
                            ),
                            'birth_at' => $this->formatDate($patient->birth_at),
                            'medical_card_url' => route(
                                'doctors.patients.medical_card',
                                ['patient' => $patient->id]
                            ),
                        ]
                        : null,
                    'assigned_at' => $assignedAt?->format('d.m.Y H:i'),
                    'stay_days' => $stayDays,
                    'stay_label' => $stayDays
                        ? $this->daysLabel($stayDays)
                        : null,
                ];
            })
            ->values();

        $items = $room->items
            ->sortBy('sort_order')
            ->map(fn ($item): array => [
                'id' => (int) $item->id,
                'type' => (string) $item->type,
                'label' => (string) ($item->label ?? ''),
                'rotation' => (float) $item->rotation,
                'style' => $this->objectStyle(
                    (float) $item->x,
                    (float) $item->y,
                    (float) $item->width,
                    (float) $item->height,
                    (float) $item->rotation,
                    $canvasWidth,
                    $canvasHeight
                ),
            ])
            ->values();

        $age = HospitalDepartmentEligibility::restrictions($room->department);

        return [
            'id' => (int) $room->id,
            'number' => (string) $room->number,
            'name' => (string) ($room->name ?? ''),
            'floor' => (string) ($room->floor ?? ''),
            'canvas_width' => $canvasWidth,
            'canvas_height' => $canvasHeight,
            'department_name' => (string) ($room->department?->name ?? ''),
            'department_age_group' => $age['group'] ?? null,
            'department_age_group_label' => $age['label'] ?? null,
            'department_number' => (string) ($room->department?->number ?? ''),
            'building_label' => $this->buildingLabel($room),
            'beds' => $beds,
            'items' => $items,
            'stats' => [
                'beds' => $beds->count(),
                'occupied' => $beds->where('occupied', true)->count(),
                'free' => $beds->where('occupied', false)->count(),
            ],
        ];
    }

    private function bedRuntimePosition(
        HospitalBed $bed,
        bool $legacyRoomCoordinates
    ): array {
        $x = (float) $bed->x;
        $y = (float) $bed->y;

        /*
         * Совместимость совпадает с Vue-редактором: если хотя бы одна
         * койка палаты имеет старые отрицательные DOM-координаты, всю
         * палату считаем сохранённой в старом формате.
         */
        if ($legacyRoomCoordinates) {
            return [$x, $y];
        }

        $width = max(1.0, (float) $bed->width);
        $height = max(1.0, (float) $bed->height);
        $angle = deg2rad($this->normalizedRotation((float) $bed->rotation));
        $cos = abs(cos($angle));
        $sin = abs(sin($angle));
        $visualWidth = $width * $cos + $height * $sin;
        $visualHeight = $width * $sin + $height * $cos;

        return [
            round($x + ($visualWidth - $width) / 2, 2),
            round($y + ($visualHeight - $height) / 2, 2),
        ];
    }

    private function objectStyle(
        float $x,
        float $y,
        float $width,
        float $height,
        float $rotation,
        int $canvasWidth,
        int $canvasHeight
    ): string {
        return implode(';', [
            'left:' . $this->percent($x, $canvasWidth) . '%',
            'top:' . $this->percent($y, $canvasHeight) . '%',
            'width:' . $this->percent($width, $canvasWidth) . '%',
            'height:' . $this->percent($height, $canvasHeight) . '%',
            'transform:rotate(' . round($rotation, 2) . 'deg)',
            '--object-rotation:' . round($rotation, 2) . 'deg',
        ]);
    }

    private function percent(float $value, int $base): string
    {
        return rtrim(
            rtrim(number_format(($value / max(1, $base)) * 100, 6, '.', ''), '0'),
            '.'
        );
    }

    private function normalizedRotation(float $rotation): float
    {
        $rotation = fmod($rotation, 360.0);

        if ($rotation < 0) {
            $rotation += 360.0;
        }

        return $rotation;
    }

    private function shortName(string $surname, string $name, string $patronym): string
    {
        $surname = trim($surname);
        $initials = '';

        if (trim($name) !== '') {
            $initials .= mb_substr(trim($name), 0, 1) . '.';
        }

        if (trim($patronym) !== '') {
            $initials .= mb_substr(trim($patronym), 0, 1) . '.';
        }

        return trim($surname . ' ' . $initials);
    }

    private function daysLabel(int $days): string
    {
        $mod10 = $days % 10;
        $mod100 = $days % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            $word = 'день';
        } elseif (
            $mod10 >= 2
            && $mod10 <= 4
            && ($mod100 < 12 || $mod100 > 14)
        ) {
            $word = 'дня';
        } else {
            $word = 'дней';
        }

        return $days . ' ' . $word;
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

    private function buildingLabel(HospitalRoom $room): string
    {
        $building = $room->department?->building;

        if (!$building) {
            return 'Корпус не указан';
        }

        return trim(
            'Корпус №' . $building->number
            . ($building->name ? ' — ' . $building->name : '')
        );
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
}
