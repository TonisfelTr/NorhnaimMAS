<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\PatientClinic;
use App\Models\RegistryRecord;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('patients'), 403);

        $patient = $user->patient()->first();

        $memberships = $patient
            ? PatientClinic::query()
                ->with(['clinic', 'doctor'])
                ->where('patient_id', (int) $patient->id)
                ->where('is_active', true)
                ->get()
                ->filter(static fn (PatientClinic $membership): bool =>
                    $membership->clinic !== null
                )
                ->values()
            : collect();

        $selectedClinicId = (int) $request->integer(
            'clinic_id',
            (int) (
                $memberships->firstWhere('is_primary', true)?->clinic_id
                ?: $patient?->clinic_id
                ?: $memberships->first()?->clinic_id
                ?: 0
            )
        );

        $selectedMembership = $memberships->first(
            static fn (PatientClinic $membership): bool =>
                (int) $membership->clinic_id === $selectedClinicId
        );

        if (!$selectedMembership && $memberships->isNotEmpty()) {
            $selectedMembership = $memberships->first();
            $selectedClinicId = (int) $selectedMembership->clinic_id;
        }

        $selectedClinic = $selectedMembership?->clinic;
        $assignedDoctor = $selectedMembership?->doctor;

        if (
            !$assignedDoctor
            && $patient
            && (int) $patient->clinic_id === $selectedClinicId
        ) {
            $assignedDoctor = $patient->doctor;
        }

        $assignedDoctorSpeciality = $this->doctorSpeciality($assignedDoctor);
        $assignedDoctorCabinet = $this->findDoctorCabinet(
            $assignedDoctor,
            $selectedClinicId > 0 ? $selectedClinicId : null
        );
        $assignedDoctorContact = $this->doctorContactData(
            $assignedDoctor,
            $assignedDoctorCabinet
        );

        $month = $this->requestedMonth($request);

        $gridStart = $month
            ->startOfMonth()
            ->startOfWeek(CarbonInterface::MONDAY);

        $gridEnd = $month
            ->endOfMonth()
            ->endOfWeek(CarbonInterface::SUNDAY);

        $appointments = $patient && $selectedClinicId > 0
            ? $this->calendarAppointments(
                (int) $patient->id,
                $selectedClinicId,
                $gridStart,
                $gridEnd
            )
            : collect();

        $appointmentsByDate = $appointments
            ->groupBy(
                static fn (array $appointment): string =>
                    $appointment['starts_at']->format('Y-m-d')
            );

        $calendarWeeks = $this->calendarWeeks(
            $gridStart,
            $gridEnd,
            $month,
            $appointmentsByDate
        );

        $nextAppointments = $patient && $selectedClinicId > 0
            ? $this->nextAppointments(
                (int) $patient->id,
                $selectedClinicId,
                5
            )
            : collect();

        $recentNotHeldAppointments = $patient && $selectedClinicId > 0
            ? $this->recentNotHeldAppointments(
                (int) $patient->id,
                $selectedClinicId,
                2
            )
            : collect();

        $displayAppointments = $recentNotHeldAppointments
            ->concat($nextAppointments)
            ->sortBy(
                static fn (array $appointment): int =>
                    $appointment['starts_at']->getTimestamp()
            )
            ->values();

        $clinicOptions = $memberships
            ->map(static function (PatientClinic $membership): array {
                $clinic = $membership->clinic;

                return [
                    'id' => (int) $membership->clinic_id,
                    'name' => (string) (
                        $clinic?->name
                        ?: 'Клиника #' . $membership->clinic_id
                    ),
                    'city' => trim((string) ($clinic?->city ?? '')),
                    'is_primary' => (bool) $membership->is_primary,
                    'clinic_type' => (string) (
                        $clinic?->clinic_type
                        ?: Clinic::TYPE_OUTPATIENT
                    ),
                ];
            })
            ->sortBy([
                ['is_primary', 'desc'],
                ['name', 'asc'],
            ])
            ->values()
            ->all();

        return view('patients.dashboard', [
            'patient' => $patient,
            'clinicOptions' => $clinicOptions,
            'selectedClinic' => $selectedClinic,
            'selectedClinicId' => $selectedClinicId,
            'assignedDoctor' => $assignedDoctor,
            'assignedDoctorSpeciality' => $assignedDoctorSpeciality,
            'assignedDoctorExperience' => $assignedDoctor?->experience ?: 'Стаж не указан',
            'assignedDoctorInitials' => $assignedDoctor?->initials ?: 'ВР',
            'assignedDoctorCabinet' => $assignedDoctorContact['cabinet'],
            'assignedDoctorPhone' => $assignedDoctorContact['phone'],
            'calendarWeeks' => $calendarWeeks,
            'nextAppointments' => $nextAppointments,
            'displayAppointments' => $displayAppointments,
            'nextAppointment' => $nextAppointments->first(),
            'monthLabel' => mb_ucfirst(
                $month->locale('ru')->translatedFormat('F Y')
            ),
            'prevMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            'todayMonth' => now()->format('Y-m'),
            'currentMonth' => $month->format('Y-m'),
            'monthAppointmentsCount' => $appointments
                ->filter(
                    static fn (array $appointment): bool =>
                        $appointment['starts_at']->format('Y-m')
                            === $month->format('Y-m')
                )
                ->count(),
        ]);
    }

    private function requestedMonth(Request $request): CarbonImmutable
    {
        $value = trim((string) $request->query('month', ''));

        if (! preg_match('/^\d{4}-\d{2}$/', $value)) {
            return CarbonImmutable::now()->startOfMonth();
        }

        try {
            $month = CarbonImmutable::createFromFormat(
                'Y-m-d',
                $value . '-01'
            );

            if (! $month) {
                return CarbonImmutable::now()->startOfMonth();
            }

            if ($month->year < 2000 || $month->year > 2100) {
                return CarbonImmutable::now()->startOfMonth();
            }

            return $month->startOfMonth();
        } catch (Throwable) {
            return CarbonImmutable::now()->startOfMonth();
        }
    }

    private function calendarAppointments(
        int $patientId,
        int $clinicId,
        CarbonImmutable $gridStart,
        CarbonImmutable $gridEnd
    ): Collection {
        /*
         * Add two days at both edges: UTC appointments are converted into their
         * saved appointment timezone before they are grouped into calendar days.
         */
        $fetchStart = $gridStart->startOfDay()->subDays(2);
        $fetchEnd = $gridEnd->endOfDay()->addDays(2);

        $query = $this->baseAppointmentQuery($patientId, $clinicId);

        if ($this->hasUtcAppointmentColumn()) {
            $query->where(
                function (Builder $builder) use (
                    $fetchStart,
                    $fetchEnd
                ): void {
                    $builder
                        ->whereBetween(
                            'appointment_starts_at_utc',
                            [
                                $fetchStart->utc(),
                                $fetchEnd->utc(),
                            ]
                        )
                        ->orWhere(
                            function (Builder $fallback) use (
                                $fetchStart,
                                $fetchEnd
                            ): void {
                                $fallback
                                    ->whereNull('appointment_starts_at_utc')
                                    ->whereBetween(
                                        'for_datetime',
                                        [$fetchStart, $fetchEnd]
                                    );
                            }
                        );
                }
            );

            $query->orderByRaw(
                'COALESCE(appointment_starts_at_utc, for_datetime) ASC'
            );
        } else {
            $query
                ->whereBetween('for_datetime', [$fetchStart, $fetchEnd])
                ->orderBy('for_datetime');
        }

        return $this->mapAppointmentRecords($query->get())
            ->filter(
                static fn (array $appointment): bool =>
                    $appointment['starts_at']->betweenIncluded(
                        $gridStart->startOfDay(),
                        $gridEnd->endOfDay()
                    )
            )
            ->values();
    }

    private function nextAppointments(
        int $patientId,
        int $clinicId,
        int $limit
    ): Collection {
        $query = $this->baseAppointmentQuery($patientId, $clinicId);

        if ($this->hasUtcAppointmentColumn()) {
            $query
                ->where(
                    function (Builder $builder): void {
                        $builder
                            ->where('appointment_starts_at_utc', '>=', now('UTC'))
                            ->orWhere(
                                function (Builder $fallback): void {
                                    $fallback
                                        ->whereNull('appointment_starts_at_utc')
                                        ->where('for_datetime', '>=', now());
                                }
                            );
                    }
                )
                ->orderByRaw(
                    'COALESCE(appointment_starts_at_utc, for_datetime) ASC'
                );
        } else {
            $query
                ->where('for_datetime', '>=', now())
                ->orderBy('for_datetime');
        }

        return $this->mapAppointmentRecords(
            $query->limit($limit)->get()
        )->values();
    }

    private function recentNotHeldAppointments(
        int $patientId,
        int $clinicId,
        int $limit
    ): Collection {
        if (
            ! Schema::hasTable('medical_encounters')
            && ! Schema::hasTable('medical_visit_confirmations')
        ) {
            return collect();
        }

        $timezone = (string) config('app.timezone', 'UTC');
        $now = CarbonImmutable::now($timezone);
        $from = $now->subDays(30)->startOfDay();

        $query = $this->baseAppointmentQuery($patientId, $clinicId);

        if ($this->hasUtcAppointmentColumn()) {
            $query->where(
                function (Builder $builder) use ($from, $now): void {
                    $builder
                        ->whereBetween(
                            'appointment_starts_at_utc',
                            [$from->utc(), $now->utc()]
                        )
                        ->orWhere(
                            function (Builder $fallback) use ($from, $now): void {
                                $fallback
                                    ->whereNull('appointment_starts_at_utc')
                                    ->whereBetween('for_datetime', [$from, $now]);
                            }
                        );
                }
            )->orderByRaw(
                'COALESCE(appointment_starts_at_utc, for_datetime) DESC'
            );
        } else {
            $query
                ->whereBetween('for_datetime', [$from, $now])
                ->orderByDesc('for_datetime');
        }

        return $this->mapAppointmentRecords($query->limit(100)->get())
            ->filter(
                static fn (array $appointment): bool =>
                    $appointment['is_not_held']
                    && $appointment['starts_at']->lte($now)
            )
            ->sortByDesc(
                static fn (array $appointment): int =>
                    $appointment['starts_at']->getTimestamp()
            )
            ->take($limit)
            ->sortBy(
                static fn (array $appointment): int =>
                    $appointment['starts_at']->getTimestamp()
            )
            ->values();
    }

    private function baseAppointmentQuery(
        int $patientId,
        ?int $clinicId = null
    ): Builder {
        $query = RegistryRecord::query()
            ->with(['doctor'])
            ->where('patient_id', $patientId);

        if ($clinicId && $clinicId > 0) {
            $query->where(
                static function (Builder $scope) use ($clinicId): void {
                    $scope
                        ->where('clinic_id', $clinicId)
                        ->orWhere(
                            static function (Builder $legacy) use ($clinicId): void {
                                $legacy
                                    ->whereNull('clinic_id')
                                    ->whereIn(
                                        'doctor_id',
                                        Doctor::query()
                                            ->select('id')
                                            ->where('clinic_id', $clinicId)
                                    );
                            }
                        );
                }
            );
        }

        return $query;
    }

    /**
     * Fetch visit outcomes in batches. Do not infer a missed appointment from
     * its date or from registry_records.appointment: only explicit visit records
     * can mark a visit as not held / a patient as absent.
     */
    private function mapAppointmentRecords(Collection $records): Collection
    {
        if ($records->isEmpty()) {
            return collect();
        }

        $ids = $records
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $encounters = collect();
        if (Schema::hasTable('medical_encounters')) {
            $encounters = DB::table('medical_encounters')
                ->whereIn('registry_record_id', $ids)
                ->get([
                    'registry_record_id',
                    'attendance_status',
                    'consultation_status',
                    'noncompletion_reason',
                ])
                ->keyBy('registry_record_id');
        }

        $confirmations = collect();
        if (Schema::hasTable('medical_visit_confirmations')) {
            $confirmations = DB::table('medical_visit_confirmations')
                ->whereIn('registry_record_id', $ids)
                ->get([
                    'registry_record_id',
                    'outcome',
                    'reason',
                ])
                ->keyBy('registry_record_id');
        }

        // Номера кабинетов и телефоны загружаем одним запросом для всех врачей
        // текущей выборки, чтобы не создавать N+1 запросов календаря.
        $cabinetsByDoctor = collect();
        $doctorIds = $records
            ->pluck('doctor_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($doctorIds !== [] && Schema::hasTable('clinic_cabinets')) {
            $cabinetQuery = DB::table('clinic_cabinets')
                ->whereIn('doctor_id', $doctorIds)
                ->where('enabled', true);

            if (Schema::hasColumn('clinic_cabinets', 'cabinet_type')) {
                $cabinetQuery->orderByRaw(
                    "CASE WHEN cabinet_type = 'doctor' THEN 0 ELSE 1 END"
                );
            }

            $cabinetsByDoctor = $cabinetQuery
                ->orderBy('id')
                ->get([
                    'doctor_id',
                    'clinic_id',
                    'cabinet',
                    'phone',
                ])
                ->groupBy('doctor_id');
        }

        $clinicNames = collect();
        $clinicIds = $records
            ->pluck('clinic_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($clinicIds !== []) {
            $clinicNames = Clinic::query()
                ->whereIn('id', $clinicIds)
                ->pluck('name', 'id');
        }

        return $records
            ->map(function (RegistryRecord $record) use (
                $encounters,
                $confirmations,
                $cabinetsByDoctor,
                $clinicNames
            ): array {
                $doctorId = (int) ($record->doctor_id ?? 0);
                $doctorCabinets = $cabinetsByDoctor->get(
                    $doctorId,
                    collect()
                );

                $clinicId = (int) (
                    $record->getRawOriginal('clinic_id')
                    ?: $record->doctor?->clinic_id
                    ?: 0
                );

                $cabinet = $clinicId > 0
                    ? $doctorCabinets->first(
                        static fn ($item): bool =>
                            (int) $item->clinic_id === $clinicId
                    )
                    : (
                        $doctorCabinets->count() === 1
                            ? $doctorCabinets->first()
                            : null
                    );

                $clinicName = $clinicId > 0
                    ? (string) (
                        $clinicNames->get($clinicId)
                        ?: 'Клиника #' . $clinicId
                    )
                    : 'Клиника не указана';

                return $this->appointmentData(
                    $record,
                    $encounters->get($record->id),
                    $confirmations->get($record->id),
                    $cabinet,
                    $clinicName
                );
            })
            ->values();
    }

    private function appointmentData(
        RegistryRecord $record,
        ?object $encounter = null,
        ?object $confirmation = null,
        ?object $cabinet = null,
        ?string $clinicName = null
    ): array {
        $startsAt = $this->appointmentMoment($record);
        $doctor = $record->doctor;

        $doctorName = $doctor
            ? trim(
                implode(
                    ' ',
                    array_filter([
                        $doctor->surname,
                        $doctor->name,
                        $doctor->patronym,
                    ])
                )
            )
            : 'Врач не указан';

        $doctorShort = $doctor
            ? trim(
                ($doctor->surname ?? '')
                . ' '
                . (
                    $doctor->name
                        ? mb_substr($doctor->name, 0, 1) . '.'
                        : ''
                )
                . ' '
                . (
                    $doctor->patronym
                        ? mb_substr($doctor->patronym, 0, 1) . '.'
                        : ''
                )
            )
            : 'Врач';

        // В Норхнейме профильные специальности хранятся в doctors.status.
        // doctors.profession — это должность (часто просто «Врач»), поэтому
        // использовать её раньше status нельзя.
        $doctorSpeciality = $this->doctorSpeciality($doctor);
        $doctorContact = $this->doctorContactData($doctor, $cabinet);

        $doctorExperience = trim((string) ($doctor?->experience ?? ''));
        if ($doctorExperience === '') {
            $doctorExperience = 'Стаж не указан';
        }

        $doctorInitials = $doctor
            ? mb_strtoupper(
                mb_substr((string) $doctor->name, 0, 1)
                . mb_substr((string) $doctor->surname, 0, 1)
            )
            : 'ВР';

        $format = (string) (
            $record->getRawOriginal('appointment_format')
            ?? 'in_person'
        );

        $attendanceStatus = (string) ($encounter->attendance_status ?? 'unknown');
        $consultationStatus = (string) ($encounter->consultation_status ?? 'not_started');
        $confirmationOutcome = (string) ($confirmation->outcome ?? '');

        // The reception button sets registry_records.appointment = true.
        // It confirms arrival only; it does not mean the consultation was held.
        $registryArrivalConfirmed = filter_var(
            $record->getRawOriginal('appointment'),
            FILTER_VALIDATE_BOOLEAN
        );

        $isMissed = $attendanceStatus === 'absent';
        $isNotHeld = $isMissed
            || $consultationStatus === 'not_held'
            || $confirmationOutcome === 'not_held';

        $isHeld = $consultationStatus === 'completed'
            || $confirmationOutcome === 'held';

        $isArrived = !$isNotHeld
            && !$isHeld
            && (
                $attendanceStatus === 'present'
                || $registryArrivalConfirmed
            );

        $isPast = $startsAt->lessThanOrEqualTo(
            CarbonImmutable::now($startsAt->getTimezone())
        );

        $isStatusUnknown = $isPast
            && !$isNotHeld
            && !$isHeld
            && !$isArrived;

        $reason = trim((string) (
            $encounter->noncompletion_reason
            ?? $confirmation->reason
            ?? ''
        ));

        if ($isMissed) {
            $statusCode = 'not_held';
            $statusMessage = 'Не состоялся: пациент не явился';
            $statusTooltip = 'Приём не состоялся — пациент не явился.';
        } elseif ($isNotHeld && $reason !== '') {
            $statusCode = 'not_held';
            $statusMessage = 'Не состоялся: ' . $reason;
            $statusTooltip = 'Приём не состоялся — ' . $reason . '.';
        } elseif ($isNotHeld) {
            $statusCode = 'not_held';
            $statusMessage = 'Приём не состоялся';
            $statusTooltip = 'Приём не состоялся — результат зафиксирован врачом.';
        } elseif ($isHeld) {
            $statusCode = 'held';
            $statusMessage = 'Консультация состоялась';
            $statusTooltip = 'Консультация состоялась — результат подтверждён врачом.';
        } elseif ($isArrived) {
            $statusCode = 'arrived';
            $statusMessage = 'Явка подтверждена, результат консультации не указан';
            $statusTooltip = 'Пациент пришёл: регистратура подтвердила явку. Результат консультации пока не указан.';
        } elseif ($isStatusUnknown) {
            $statusCode = 'unknown';
            $statusMessage = 'Статус не указан: явка и результат приёма не зафиксированы';
            $statusTooltip = 'Время приёма прошло, но явка пациента и результат консультации не зафиксированы.';
        } else {
            $statusCode = 'upcoming';
            $statusMessage = null;
            $statusTooltip = $doctorName . ' — ' . (
                $format === 'telemedicine'
                    ? 'телемедицина'
                    : 'очный приём'
            );
        }

        return [
            'id' => (int) $record->id,
            'starts_at' => $startsAt,
            'doctor_name' => $doctorName,
            'doctor_short' => $doctorShort,
            'doctor_experience' => $doctorExperience,
            'doctor_speciality' => $doctorSpeciality,
            'doctor_initials' => $doctorInitials,
            'doctor_cabinet' => $doctorContact['cabinet'],
            'doctor_phone' => $doctorContact['phone'],
            'clinic_id' => (int) ($record->clinic_id ?? 0),
            'clinic_name' => $clinicName ?: 'Клиника не указана',
            'format' => $format,
            'format_label' => $format === 'telemedicine'
                ? 'Телемедицина'
                : 'Очный приём',
            'duration_minutes' => (
                $record->getRawOriginal('appointment_duration_minutes')
                    ? (int) $record->getRawOriginal('appointment_duration_minutes')
                    : null
            ),
            'is_missed' => $isMissed,
            'is_not_held' => $isNotHeld,
            'is_held' => $isHeld && !$isNotHeld,
            'is_arrived' => $isArrived,
            'is_status_unknown' => $isStatusUnknown,
            'status_code' => $statusCode,
            'status_message' => $statusMessage,
            'status_tooltip' => $statusTooltip,
        ];
    }

    /**
     * Специальности в профиле клиники хранятся в doctors.status.
     * doctors.profession — должность, поэтому значение «Врач» не должно
     * подменять специальность психиатра/психотерапевта.
     */
    private function doctorSpeciality(?Doctor $doctor): string
    {
        if (!$doctor) {
            return 'Специализация не указана';
        }

        $status = trim((string) ($doctor->status ?? ''));

        if ($status !== '') {
            $specialities = collect(
                preg_split('/\s*[,;|]\s*/u', $status, -1, PREG_SPLIT_NO_EMPTY) ?: []
            )
                ->map(static fn ($value): string => trim((string) $value))
                ->filter()
                ->reject(
                    static fn (string $value): bool =>
                        mb_strtolower($value) === 'врач'
                )
                ->unique(
                    static fn (string $value): string =>
                        mb_strtolower($value)
                )
                ->values()
                ->implode(', ');

            if ($specialities !== '') {
                return $specialities;
            }
        }

        foreach ([
            $doctor->accreditation_speciality ?? null,
            $doctor->profession ?? null,
        ] as $fallback) {
            $fallback = trim((string) $fallback);

            if ($fallback !== '' && mb_strtolower($fallback) !== 'врач') {
                return $fallback;
            }
        }

        return 'Специализация не указана';
    }

    private function findDoctorCabinet(
        ?Doctor $doctor,
        ?int $clinicId = null
    ): ?object {
        if (!$doctor || !Schema::hasTable('clinic_cabinets')) {
            return null;
        }

        $clinicId = $clinicId ?: (
            $doctor->clinic_id ? (int) $doctor->clinic_id : null
        );

        $query = DB::table('clinic_cabinets')
            ->where('doctor_id', $doctor->getKey())
            ->where('enabled', true);

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        if (Schema::hasColumn('clinic_cabinets', 'cabinet_type')) {
            $query->orderByRaw(
                "CASE WHEN cabinet_type = 'doctor' THEN 0 ELSE 1 END"
            );
        }

        $cabinets = $query
            ->orderBy('id')
            ->get([
                'clinic_id',
                'cabinet',
                'phone',
            ]);

        // If the doctor is not tied to a known clinic, do not guess between
        // several offices belonging to different clinics.
        if (!$clinicId && $cabinets->count() !== 1) {
            return null;
        }

        return $cabinets->first();
    }

    private function doctorContactData(
        ?Doctor $doctor,
        ?object $cabinet = null
    ): array {
        $cabinetNumber = trim((string) ($cabinet->cabinet ?? ''));
        $phone = trim((string) ($cabinet->phone ?? ''));

        if ($phone === '') {
            $phone = trim((string) ($doctor?->phone ?? ''));
        }

        return [
            'cabinet' => $cabinetNumber !== ''
                ? '№ ' . $cabinetNumber
                : '',
            'phone' => $phone,
        ];
    }

    private function appointmentMoment(
        RegistryRecord $record
    ): CarbonImmutable {
        $utc = $record->getRawOriginal('appointment_starts_at_utc');

        if ($utc) {
            $timezone = trim(
                (string) $record->getRawOriginal('appointment_timezone')
            );

            if ($timezone === '') {
                $timezone = (string) config('app.timezone', 'UTC');
            }

            try {
                return CarbonImmutable::parse(
                    (string) $utc,
                    'UTC'
                )->setTimezone($timezone);
            } catch (Throwable) {
                // Fall through to the legacy for_datetime value.
            }
        }

        return CarbonImmutable::parse(
            (string) $record->getRawOriginal('for_datetime')
        );
    }

    private function calendarWeeks(
        CarbonImmutable $gridStart,
        CarbonImmutable $gridEnd,
        CarbonImmutable $month,
        Collection $appointmentsByDate
    ): array {
        $weeks = [];
        $week = [];

        for (
            $day = $gridStart;
            $day->lte($gridEnd);
            $day = $day->addDay()
        ) {
            $dateKey = $day->format('Y-m-d');

            $week[] = [
                'date' => $day,
                'date_key' => $dateKey,
                'in_month' =>
                    $day->format('Y-m')
                    === $month->format('Y-m'),
                'is_today' => $day->isToday(),
                'appointments' =>
                    $appointmentsByDate->get($dateKey, collect()),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        return $weeks;
    }

    private function hasUtcAppointmentColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn(
                'registry_records',
                'appointment_starts_at_utc'
            );
        }

        return $hasColumn;
    }
}
