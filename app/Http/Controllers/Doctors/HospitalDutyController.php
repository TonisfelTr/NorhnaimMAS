<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\EmployeeVacation;
use App\Models\HospitalDepartment;
use App\Models\HospitalDuty;
use App\Models\Nurse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class HospitalDutyController extends Controller
{
    private const EMPLOYEE_TYPES = [
        'doctor',
        'nurse',
    ];

    private const ABSENCE_STATUSES = [
        'planned',
        'approved',
    ];

    private const ABSENCE_TYPE_LABELS = [
        'annual' => 'ежегодный отпуск',
        'study' => 'учебный отпуск',
        'unpaid' => 'отпуск без сохранения',
        'maternity' => 'декретный отпуск',
        'other' => 'другое отсутствие / отгул',
    ];

    private const DOCTOR_EXCLUDED_POSITIONS = [
        'Старшая медицинская сестра',
        'Медицинская сестра',
        'Процедурная медицинская сестра',
        'Медицинский регистратор',
        'Санитар',
    ];

    public function index(Request $request)
    {
        [$doctor, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $weekStart = $this->resolveWeekStart(
            $request->string('week')->toString()
        );
        $weekEndExclusive = $weekStart->copy()->addDays(7);

        $selectedDepartmentId = $request->integer('department_id');
        $selectedEmployeeKey = trim(
            $request->string('employee')->toString()
        );

        $departments = HospitalDepartment::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('number')
            ->orderBy('name')
            ->get([
                'id',
                'number',
                'name',
            ]);

        if (
            $selectedDepartmentId > 0
            && !$departments->contains(
                static fn (HospitalDepartment $department): bool =>
                    (int) $department->id === $selectedDepartmentId
            )
        ) {
            $selectedDepartmentId = 0;
        }

        $employees = $this->employeesForClinic($clinicId);
        $employeeMap = $employees->keyBy('key');

        /*
         * employee_vacations у нас является общим реестром отсутствий:
         * ежегодный/учебный/без сохранения/декретный/другое (в т.ч. отгул).
         * Для графика дежурств учитываем и planned, и approved — если период
         * отсутствия уже внесён в график, сотрудника на пересекающееся
         * дежурство предлагать нельзя.
         */
        $employeeAbsenceRanges = $this->employeeAbsenceRanges(
            $clinicId,
            $weekStart->copy()->subDays(3),
            $weekEndExclusive->copy()->addDays(3)
        );

        /*
         * В верхнем фильтре не показываем сотрудников, у которых есть
         * отсутствие хотя бы на части выбранной недели. В модалке остаётся
         * полный кадровый список, но JS скрывает недоступных уже по точному
         * интервалу конкретного дежурства.
         */
        $filterEmployees = $employees
            ->reject(
                fn (array $employee): bool =>
                    $this->employeeHasAbsenceInRange(
                        $employeeAbsenceRanges[$employee['key']] ?? [],
                        $weekStart,
                        $weekEndExclusive
                    )
            )
            ->values();

        if (
            $selectedEmployeeKey !== ''
            && !$filterEmployees->contains(
                static fn (array $employee): bool =>
                    $employee['key'] === $selectedEmployeeKey
            )
        ) {
            $selectedEmployeeKey = '';
        }

        $query = HospitalDuty::query()
            ->with('department')
            ->where('clinic_id', $clinicId)
            ->where('starts_at', '<', $weekEndExclusive)
            ->where('ends_at', '>', $weekStart)
            ->orderBy('starts_at')
            ->orderBy('ends_at');

        if ($selectedDepartmentId > 0) {
            $query->where('department_id', $selectedDepartmentId);
        }

        $employeeFilter = $this->parseEmployeeKey(
            $selectedEmployeeKey
        );

        if ($employeeFilter) {
            $query
                ->where('employee_type', $employeeFilter['type'])
                ->where('employee_id', $employeeFilter['id']);
        }

        $duties = Schema::hasTable('hospital_duties')
            ? $query->get()
            : collect();

        $now = now();

        $dutyPayloads = $duties
            ->map(
                function (HospitalDuty $duty) use (
                    $employeeMap,
                    $now
                ): array {
                    $employee = $employeeMap->get(
                        $duty->employee_type
                        . ':'
                        . $duty->employee_id
                    );

                    return $this->dutyPayload(
                        $duty,
                        $employee,
                        $now
                    );
                }
            )
            ->values();

        $days = collect(range(0, 6))
            ->map(
                function (int $offset) use (
                    $weekStart,
                    $dutyPayloads,
                    $now
                ): array {
                    $date = $weekStart->copy()->addDays($offset);
                    $dayStart = $date->copy()->startOfDay();
                    $dayEnd = $dayStart->copy()->addDay();

                    $dayDuties = $dutyPayloads
                        ->filter(
                            static function (array $duty) use (
                                $dayStart,
                                $dayEnd
                            ): bool {
                                $startsAt = Carbon::parse(
                                    $duty['starts_at_iso']
                                );
                                $endsAt = Carbon::parse(
                                    $duty['ends_at_iso']
                                );

                                return $startsAt->lt($dayEnd)
                                    && $endsAt->gt($dayStart);
                            }
                        )
                        ->map(
                            fn (array $duty): array =>
                                $this->dutyForDay(
                                    $duty,
                                    $dayStart,
                                    $dayEnd
                                )
                        )
                        ->values();

                    return [
                        'date' => $date->toDateString(),
                        'weekday' => mb_strtoupper(
                            $date
                                ->locale('ru')
                                ->translatedFormat('D')
                        ),
                        'day' => $date->format('d'),
                        'month' => $date
                            ->locale('ru')
                            ->translatedFormat('F'),
                        'is_today' => $date->isSameDay($now),
                        'duties' => $dayDuties,
                    ];
                }
            )
            ->values();

        $allWeekDuties = Schema::hasTable('hospital_duties')
            ? HospitalDuty::query()
                ->where('clinic_id', $clinicId)
                ->where('starts_at', '<', $weekEndExclusive)
                ->where('ends_at', '>', $weekStart)
                ->get()
            : collect();

        $stats = [
            'duties' => $allWeekDuties->count(),
            'employees' => $allWeekDuties
                ->map(
                    static fn (HospitalDuty $duty): string =>
                        $duty->employee_type . ':' . $duty->employee_id
                )
                ->unique()
                ->count(),
            'departments' => $allWeekDuties
                ->pluck('department_id')
                ->unique()
                ->count(),
            'active_now' => $allWeekDuties
                ->filter(
                    static fn (HospitalDuty $duty): bool =>
                        $duty->starts_at->lte($now)
                        && $duty->ends_at->gt($now)
                )
                ->count(),
        ];

        $filterParameters = [
            'department_id' => $selectedDepartmentId > 0
                ? $selectedDepartmentId
                : null,
            'employee' => $selectedEmployeeKey !== ''
                ? $selectedEmployeeKey
                : null,
        ];

        return view(
            'doctors.settings.duty_schedule',
            [
                'settingsDoctor' => $doctor,
                'settingsIsPrivatePractice' => false,
                'settingsIsInpatient' => true,
                'settingsContextLabel' => 'Стационар',
                'settingsContextIcon' => 'bi-hospital',
                'clinicName' => $clinic->name,
                'departments' => $departments,
                'employees' => $employees,
                'filterEmployees' => $filterEmployees,
                'employeeAbsenceRanges' => $employeeAbsenceRanges,
                'days' => $days,
                'stats' => $stats,
                'selectedDepartmentId' => $selectedDepartmentId,
                'selectedEmployeeKey' => $selectedEmployeeKey,
                'weekStart' => $weekStart->toDateString(),
                'weekLabel' => $this->weekLabel($weekStart),
                'previousWeekUrl' => $this->weekUrl(
                    $weekStart->copy()->subWeek(),
                    $filterParameters
                ),
                'nextWeekUrl' => $this->weekUrl(
                    $weekStart->copy()->addWeek(),
                    $filterParameters
                ),
                'currentWeekUrl' => $this->weekUrl(
                    now()->startOfWeek(Carbon::MONDAY),
                    $filterParameters
                ),
                'dutyStoreUrl' => route(
                    'doctors.settings.duty-schedule.store'
                ),
                'dutyUpdateUrlTemplate' => route(
                    'doctors.settings.duty-schedule.update',
                    ['duty' => '__DUTY__']
                ),
                'dutyDeleteUrlTemplate' => route(
                    'doctors.settings.duty-schedule.delete',
                    ['duty' => '__DUTY__']
                ),
                'csrfToken' => csrf_token(),
                'schemaReady' => Schema::hasTable('hospital_duties'),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $this->ensureSchemaReady();

        $data = $this->validatedDutyData(
            $request,
            $clinicId
        );

        $this->ensureEmployeeHasNoOverlap(
            $clinicId,
            $data['employee_type'],
            (int) $data['employee_id'],
            $data['starts_at'],
            $data['ends_at']
        );

        $duty = HospitalDuty::create([
            'clinic_id' => $clinicId,
            'department_id' => (int) $data['department_id'],
            'employee_type' => $data['employee_type'],
            'employee_id' => (int) $data['employee_id'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'comment' => $this->nullableTrim(
                $data['comment'] ?? null
            ),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'id' => (int) $duty->id,
            'message' => 'Дежурство добавлено.',
        ]);
    }

    public function update(
        Request $request,
        int $duty
    ): JsonResponse {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $this->ensureSchemaReady();

        $model = $this->dutyForClinic(
            $duty,
            $clinicId
        );

        $data = $this->validatedDutyData(
            $request,
            $clinicId
        );

        $this->ensureEmployeeHasNoOverlap(
            $clinicId,
            $data['employee_type'],
            (int) $data['employee_id'],
            $data['starts_at'],
            $data['ends_at'],
            (int) $model->id
        );

        $model->update([
            'department_id' => (int) $data['department_id'],
            'employee_type' => $data['employee_type'],
            'employee_id' => (int) $data['employee_id'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'comment' => $this->nullableTrim(
                $data['comment'] ?? null
            ),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Дежурство обновлено.',
        ]);
    }

    public function destroy(int $duty): JsonResponse
    {
        [, $clinic] = $this->currentInpatientClinic();
        $clinicId = (int) $clinic->id;

        $this->ensureSchemaReady();

        $model = $this->dutyForClinic(
            $duty,
            $clinicId
        );
        $model->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Дежурство удалено.',
        ]);
    }

    private function currentInpatientClinic(): array
    {
        $doctor = auth()->user()?->doctor;

        abort_unless(
            $doctor && (int) $doctor->clinic_id > 0,
            403
        );

        $clinic = Clinic::findOrFail(
            (int) $doctor->clinic_id
        );

        abort_unless(
            (string) $clinic->clinic_type === Clinic::TYPE_INPATIENT,
            404
        );

        return [$doctor, $clinic];
    }

    private function resolveWeekStart(string $value): Carbon
    {
        try {
            $date = $value !== ''
                ? Carbon::parse($value)
                : now();
        } catch (Throwable) {
            $date = now();
        }

        return $date
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();
    }

    private function weekLabel(Carbon $weekStart): string
    {
        $weekEnd = $weekStart->copy()->addDays(6);

        if ($weekStart->isSameMonth($weekEnd)) {
            return $weekStart->format('d')
                . '–'
                . $weekEnd->format('d')
                . ' '
                . $weekEnd
                    ->locale('ru')
                    ->translatedFormat('F Y');
        }

        return $weekStart
                ->locale('ru')
                ->translatedFormat('d F')
            . ' — '
            . $weekEnd
                ->locale('ru')
                ->translatedFormat('d F Y');
    }

    private function weekUrl(
        Carbon $weekStart,
        array $filters
    ): string {
        return route(
            'doctors.settings.duty-schedule',
            array_filter(
                [
                    'week' => $weekStart->toDateString(),
                    ...$filters,
                ],
                static fn ($value): bool =>
                    $value !== null && $value !== ''
            )
        );
    }

    private function employeesForClinic(int $clinicId): Collection
    {
        $doctorEmployees = Doctor::query()
            ->where('clinic_id', $clinicId)
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('profession')
                        ->orWhereNotIn(
                            'profession',
                            self::DOCTOR_EXCLUDED_POSITIONS
                        );
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get()
            ->map(
                fn (Doctor $doctor): array =>
                    $this->doctorPayload($doctor)
            );

        $nurseEmployees = Nurse::query()
            ->where('clinic_id', $clinicId)
            ->orderBy('surname')
            ->orderBy('name')
            ->get()
            ->map(
                fn (Nurse $nurse): array =>
                    $this->nursePayload($nurse)
            );

        return $doctorEmployees
            ->concat($nurseEmployees)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function doctorPayload(Doctor $doctor): array
    {
        $name = trim(implode(' ', array_filter([
            $doctor->surname ?? null,
            $doctor->name ?? null,
            $doctor->patronym ?? null,
        ])));

        $role = trim((string) ($doctor->status ?? ''));

        if ($role === '') {
            $role = trim((string) ($doctor->profession ?? ''));
        }

        return [
            'key' => 'doctor:' . $doctor->id,
            'type' => 'doctor',
            'id' => (int) $doctor->id,
            'name' => $name !== ''
                ? $name
                : 'Врач #' . $doctor->id,
            'role' => $role !== ''
                ? $role
                : 'Врач',
            'category' => 'doctor',
        ];
    }

    private function nursePayload(Nurse $nurse): array
    {
        $name = trim(implode(' ', array_filter([
            $nurse->surname ?? null,
            $nurse->name ?? null,
            $nurse->patronym ?? null,
        ])));

        $role = trim((string) ($nurse->profession ?? ''));

        return [
            'key' => 'nurse:' . $nurse->id,
            'type' => 'nurse',
            'id' => (int) $nurse->id,
            'name' => $name !== ''
                ? $name
                : 'Медсестра #' . $nurse->id,
            'role' => $role !== ''
                ? $role
                : 'Медицинская сестра',
            'category' => 'nurse',
        ];
    }

    private function parseEmployeeKey(
        string $key
    ): ?array {
        if ($key === '' || !str_contains($key, ':')) {
            return null;
        }

        [$type, $id] = explode(':', $key, 2);
        $id = (int) $id;

        if (
            !in_array($type, self::EMPLOYEE_TYPES, true)
            || $id <= 0
        ) {
            return null;
        }

        return [
            'type' => $type,
            'id' => $id,
        ];
    }

    private function validatedDutyData(
        Request $request,
        int $clinicId
    ): array {
        $data = $request->validate([
            'department_id' => [
                'required',
                'integer',
                Rule::exists('hospital_departments', 'id')
                    ->where('clinic_id', $clinicId)
                    ->where('enabled', true),
            ],
            'employee_type' => [
                'required',
                Rule::in(self::EMPLOYEE_TYPES),
            ],
            'employee_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'starts_at' => [
                'required',
                'date',
            ],
            'ends_at' => [
                'required',
                'date',
                'after:starts_at',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'department_id.required' => 'Выберите отделение.',
            'employee_type.required' => 'Выберите сотрудника.',
            'employee_id.required' => 'Выберите сотрудника.',
            'starts_at.required' => 'Укажите начало дежурства.',
            'ends_at.required' => 'Укажите окончание дежурства.',
            'ends_at.after' => 'Окончание дежурства должно быть позже начала.',
        ]);

        $this->ensureEmployeeBelongsToClinic(
            $clinicId,
            $data['employee_type'],
            (int) $data['employee_id']
        );

        $start = Carbon::parse($data['starts_at']);
        $end = Carbon::parse($data['ends_at']);

        $this->ensureEmployeeNotAbsent(
            $clinicId,
            $data['employee_type'],
            (int) $data['employee_id'],
            $start,
            $end
        );

        if ($start->diffInMinutes($end) > 72 * 60) {
            throw ValidationException::withMessages([
                'ends_at' => 'Одно дежурство не может длиться больше 72 часов.',
            ]);
        }

        return $data;
    }

    private function ensureEmployeeBelongsToClinic(
        int $clinicId,
        string $employeeType,
        int $employeeId
    ): void {
        $exists = match ($employeeType) {
            'doctor' => Doctor::query()
                ->where('clinic_id', $clinicId)
                ->where('id', $employeeId)
                ->where(
                    static function ($query): void {
                        $query
                            ->whereNull('profession')
                            ->orWhereNotIn(
                                'profession',
                                self::DOCTOR_EXCLUDED_POSITIONS
                            );
                    }
                )
                ->exists(),
            'nurse' => Nurse::query()
                ->where('clinic_id', $clinicId)
                ->where('id', $employeeId)
                ->exists(),
            default => false,
        };

        if (!$exists) {
            throw ValidationException::withMessages([
                'employee_id' => 'Выбранный сотрудник не относится к этой клинике.',
            ]);
        }
    }

    private function employeeAbsenceRanges(
        int $clinicId,
        Carbon $from,
        Carbon $to
    ): array {
        if (!Schema::hasTable('employee_vacations')) {
            return [];
        }

        return EmployeeVacation::query()
            ->where('clinic_id', $clinicId)
            ->whereIn('status', self::ABSENCE_STATUSES)
            ->whereDate('date_from', '<', $to->toDateString())
            ->whereDate('date_to', '>=', $from->toDateString())
            ->orderBy('date_from')
            ->get()
            ->groupBy(
                static fn (EmployeeVacation $absence): string =>
                    $absence->employee_type . ':' . $absence->employee_id
            )
            ->map(
                static fn (Collection $absences): array =>
                    $absences
                        ->map(
                            static fn (EmployeeVacation $absence): array => [
                                'from' => Carbon::parse($absence->date_from)->toDateString(),
                                'to' => Carbon::parse($absence->date_to)->toDateString(),
                                'type' => (string) $absence->vacation_type,
                                'status' => (string) $absence->status,
                            ]
                        )
                        ->values()
                        ->all()
            )
            ->all();
    }

    private function employeeHasAbsenceInRange(
        array $ranges,
        Carbon $from,
        Carbon $to
    ): bool {
        foreach ($ranges as $range) {
            if (empty($range['from']) || empty($range['to'])) {
                continue;
            }

            $absenceStart = Carbon::parse($range['from'])->startOfDay();
            $absenceEndExclusive = Carbon::parse($range['to'])
                ->addDay()
                ->startOfDay();

            if ($absenceStart->lt($to) && $absenceEndExclusive->gt($from)) {
                return true;
            }
        }

        return false;
    }

    private function ensureEmployeeNotAbsent(
        int $clinicId,
        string $employeeType,
        int $employeeId,
        Carbon $startsAt,
        Carbon $endsAt
    ): void {
        if (!Schema::hasTable('employee_vacations')) {
            return;
        }

        $absences = EmployeeVacation::query()
            ->where('clinic_id', $clinicId)
            ->where('employee_type', $employeeType)
            ->where('employee_id', $employeeId)
            ->whereIn('status', self::ABSENCE_STATUSES)
            ->whereDate('date_from', '<=', $endsAt->toDateString())
            ->whereDate('date_to', '>=', $startsAt->toDateString())
            ->get();

        foreach ($absences as $absence) {
            $absenceStart = Carbon::parse($absence->date_from)->startOfDay();
            $absenceEndExclusive = Carbon::parse($absence->date_to)
                ->addDay()
                ->startOfDay();

            if (
                $absenceStart->lt($endsAt)
                && $absenceEndExclusive->gt($startsAt)
            ) {
                $typeLabel = self::ABSENCE_TYPE_LABELS[
                    (string) $absence->vacation_type
                ] ?? 'отсутствие';

                throw ValidationException::withMessages([
                    'employee_id' => sprintf(
                        'Сотрудник недоступен: %s с %s по %s. Выберите другого сотрудника.',
                        $typeLabel,
                        $absenceStart->format('d.m.Y'),
                        $absenceEndExclusive->copy()->subDay()->format('d.m.Y')
                    ),
                ]);
            }
        }
    }

    private function ensureEmployeeHasNoOverlap(
        int $clinicId,
        string $employeeType,
        int $employeeId,
        string $startsAt,
        string $endsAt,
        ?int $ignoreDutyId = null
    ): void {
        $overlap = HospitalDuty::query()
            ->where('clinic_id', $clinicId)
            ->where('employee_type', $employeeType)
            ->where('employee_id', $employeeId)
            ->when(
                $ignoreDutyId,
                static fn ($query) =>
                    $query->where('id', '<>', $ignoreDutyId)
            )
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'starts_at' => 'У сотрудника уже есть дежурство, пересекающееся с этим временем.',
            ]);
        }
    }

    private function dutyForClinic(
        int $dutyId,
        int $clinicId
    ): HospitalDuty {
        return HospitalDuty::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail($dutyId);
    }

    private function dutyPayload(
        HospitalDuty $duty,
        ?array $employee,
        Carbon $now
    ): array {
        return [
            'id' => (int) $duty->id,
            'department_id' => (int) $duty->department_id,
            'department_name' => (string) (
                $duty->department?->name
                ?? 'Отделение #' . $duty->department_id
            ),
            'employee_type' => (string) $duty->employee_type,
            'employee_id' => (int) $duty->employee_id,
            'employee_key' => $duty->employee_type
                . ':'
                . $duty->employee_id,
            'employee_name' => $employee['name']
                ?? 'Сотрудник #' . $duty->employee_id,
            'employee_role' => $employee['role'] ?? '',
            'starts_at' => $duty->starts_at->format('Y-m-d\TH:i'),
            'ends_at' => $duty->ends_at->format('Y-m-d\TH:i'),
            'starts_at_iso' => $duty->starts_at->toIso8601String(),
            'ends_at_iso' => $duty->ends_at->toIso8601String(),
            'comment' => (string) ($duty->comment ?? ''),
            'is_active' => $duty->starts_at->lte($now)
                && $duty->ends_at->gt($now),
        ];
    }

    private function dutyForDay(
        array $duty,
        Carbon $dayStart,
        Carbon $dayEnd
    ): array {
        $startsAt = Carbon::parse($duty['starts_at_iso']);
        $endsAt = Carbon::parse($duty['ends_at_iso']);

        if ($startsAt->lt($dayStart)) {
            $timeLabel = 'до ' . $endsAt->format('H:i');
        } elseif ($endsAt->gt($dayEnd)) {
            $timeLabel = $startsAt->format('H:i') . ' →';
        } elseif (!$startsAt->isSameDay($endsAt)) {
            $timeLabel = $startsAt->format('H:i')
                . ' → '
                . $endsAt->format('H:i');
        } else {
            $timeLabel = $startsAt->format('H:i')
                . '–'
                . $endsAt->format('H:i');
        }

        return [
            ...$duty,
            'day_time_label' => $timeLabel,
        ];
    }

    private function ensureSchemaReady(): void
    {
        abort_unless(
            Schema::hasTable('hospital_duties'),
            503,
            'Таблица дежурств ещё не создана. Выполните миграции.'
        );
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== ''
            ? $value
            : null;
    }
}
