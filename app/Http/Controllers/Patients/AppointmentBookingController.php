<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicCabinet;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientClinic;
use App\Models\RegistryRecord;
use App\Services\ClinicTelemedicineSettings;
use App\Services\DoctorAppointmentAvailability;
use App\Services\RegistryAppointmentConflictService;
use App\Services\PatientMonthlyAppointmentLimit;
use App\Support\ClinicAppointmentTime;
use App\Support\DoctorAppointmentTimezone;
use App\Support\DoctorTelemedicineEligibility;
use App\Support\PatientCareScope;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentBookingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        $patient = $user->patient()->first();
        $isInpatient = $patient
            ? PatientCareScope::isCurrentlyInpatient((int) $patient->id)
            : false;

        // Existing memberships are used only to choose a convenient default.
        // They no longer restrict the clinic catalogue available for search.
        $memberships = $patient
            ? PatientClinic::query()
                ->with(['clinic', 'doctor'])
                ->where('patient_id', (int) $patient->id)
                ->where('is_active', true)
                ->get()
                ->filter(static fn (PatientClinic $membership): bool =>
                    $membership->clinic !== null
                    && $membership->clinic->clinic_type !== Clinic::TYPE_INPATIENT
                )
                ->values()
            : collect();

        $defaultMembership = $memberships->firstWhere('is_primary', true);

        if (!$defaultMembership && $patient?->clinic_id) {
            $defaultMembership = $memberships->first(
                static fn (PatientClinic $membership): bool =>
                    (int) $membership->clinic_id === (int) $patient->clinic_id
            );
        }

        $defaultClinicId = (int) (
            $defaultMembership?->clinic_id
            ?: $patient?->clinic_id
            ?: 0
        );

        $requestedClinicId = (int) $request->integer('clinic_id');
        $selectedClinicId = $requestedClinicId > 0
            ? $requestedClinicId
            : $defaultClinicId;

        $clinic = null;

        if ($selectedClinicId > 0) {
            $clinic = Clinic::query()
                ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
                ->whereNull('deleted_at')
                ->find($selectedClinicId);

            // A manually supplied inpatient, deleted or unknown clinic ID
            // must never become a valid booking target. A stale legacy default,
            // however, should not block the patient from searching another clinic.
            if ($requestedClinicId > 0) {
                abort_unless($clinic, 404);
            }
        }

        $selectedClinicId = (int) ($clinic?->id ?? 0);

        $selectedMembership = $memberships->first(
            static fn (PatientClinic $membership): bool =>
                (int) $membership->clinic_id === (int) $selectedClinicId
        );

        $assignedDoctor = $selectedMembership?->doctor;

        if (
            !$assignedDoctor
            && $patient
            && (int) $patient->clinic_id === (int) $selectedClinicId
        ) {
            $assignedDoctor = $patient->doctor;
        }

        $timezone = $clinic
            ? ClinicAppointmentTime::timezone($clinic)
            : (string) config('app.timezone', 'UTC');

        $doctors = $clinic && !$isInpatient
            ? $this->doctorOptions($clinic, $assignedDoctor?->id)
            : [];

        $selectedDoctorId = (int) old(
            'doctor_id',
            collect($doctors)->firstWhere('assigned', true)['id']
                ?? ($doctors[0]['id'] ?? 0)
        );

        $selectedDoctor = collect($doctors)->firstWhere(
            'id',
            $selectedDoctorId
        );
        $selectedFormats = $selectedDoctor['formats'] ?? [];
        $selectedFormat = (string) old(
            'appointment_format',
            in_array('in_person', $selectedFormats, true)
                ? 'in_person'
                : ($selectedFormats[0] ?? '')
        );

        $blockedReason = null;

        if (!$patient) {
            $blockedReason = 'Сначала привяжите медицинскую карту в разделе «Мой профиль».';
        } elseif ($isInpatient) {
            $blockedReason = 'Во время госпитализации запись на амбулаторный приём через личный кабинет недоступна.';
        }

        return view('patients.calendar.index', [
            'patient' => $patient,
            'clinic' => $clinic,
            'selectedClinicId' => $selectedClinicId,
            'doctors' => $doctors,
            'selectedDoctorId' => $selectedDoctorId,
            'selectedFormats' => $selectedFormats,
            'selectedFormat' => $selectedFormat,
            'todayDate' => now($timezone)->toDateString(),
            'timezone' => $timezone,
            'durationMinutes' => $this->defaultDuration(),
            'blockedReason' => $blockedReason,
            'upcomingAppointments' => $patient
                ? $this->upcomingAppointments($patient)
                : [],
        ]);
    }

    /**
     * Search the complete active outpatient clinic catalogue.
     * Search is text-based and paginated; the patient does not need a
     * patient_clinics membership before selecting a clinic.
     */
    public function searchClinics(Request $request): JsonResponse
    {
        $values = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $term = trim(preg_replace('/\s+/u', ' ', (string) $values['q']) ?? '');
        if (mb_strlen($term) < 2) {
            return response()->json([
                'groups' => [],
                'has_more' => false,
                'page' => 1,
            ]);
        }

        $page = max(1, (int) ($values['page'] ?? 1));
        $perPage = 20;
        $like = '%' . $term . '%';

        $rows = Clinic::query()
            ->leftJoin('clinics as parent_clinics', function ($join): void {
                $join->on('parent_clinics.id', '=', 'clinics.parent_clinic_id')
                    ->where('parent_clinics.clinic_type', Clinic::TYPE_OUTPATIENT)
                    ->whereNull('parent_clinics.deleted_at');
            })
            ->where('clinics.clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('clinics.deleted_at')
            ->where(function ($query) use ($like): void {
                $query
                    ->where('clinics.name', 'ilike', $like)
                    ->orWhere('clinics.address', 'ilike', $like)
                    ->orWhere('clinics.city', 'ilike', $like)
                    ->orWhere('parent_clinics.name', 'ilike', $like)
                    ->orWhere('parent_clinics.address', 'ilike', $like)
                    ->orWhere('parent_clinics.city', 'ilike', $like);
            })
            ->select([
                'clinics.id',
                'clinics.name',
                'clinics.address',
                'clinics.city',
                'clinics.parent_clinic_id',
                'parent_clinics.id as parent_id',
                'parent_clinics.name as parent_name',
            ])
            ->orderByRaw('COALESCE(parent_clinics.name, clinics.name)')
            ->orderBy('clinics.city')
            ->orderBy('clinics.name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage + 1)
            ->get();

        $hasMore = $rows->count() > $perPage;
        $items = $rows->take($perPage);

        $groups = $items
            ->groupBy(static fn ($clinic): int =>
                (int) ($clinic->parent_id ?: $clinic->id)
            )
            ->map(static function ($group): array {
                $first = $group->first();

                return [
                    'id' => (int) ($first->parent_id ?: $first->id),
                    'name' => (string) ($first->parent_name ?: $first->name),
                    'clinics' => $group->map(static fn ($clinic): array => [
                        'id' => (int) $clinic->id,
                        'name' => (string) $clinic->name,
                        'city' => (string) ($clinic->city ?? ''),
                        'address' => (string) ($clinic->address ?? ''),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'groups' => $groups,
            'has_more' => $hasMore,
            'page' => $page,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function availability(
        Request $request,
        DoctorAppointmentAvailability $availability,
        RegistryAppointmentConflictService $conflicts,
        PatientMonthlyAppointmentLimit $monthlyLimit
    ): JsonResponse {
        $values = $request->validate([
            'clinic_id' => ['required', 'integer', 'min:1'],
            'doctor_id' => ['required', 'integer', 'min:1'],
            'from_date' => ['required', 'date_format:Y-m-d'],
            'appointment_format' => ['required', 'in:in_person,telemedicine'],
            'patient_timezone' => ['required', 'string', 'max:64'],
        ]);

        [$patient, $clinic] = $this->bookingContext($request);
        $clinicId = (int) $clinic->id;
        $doctor = Doctor::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail((int) $values['doctor_id']);

        $format = (string) $values['appointment_format'];
        $this->assertDoctorCanBook($doctor, $clinic, $format);

        $duration = $this->defaultDuration();
        $clinicTimezone = ClinicAppointmentTime::timezone($clinic);
        $patientTimezone = $this->normalizePatientTimezone(
            (string) $values['patient_timezone']
        );
        $doctorTimezone = DoctorAppointmentTimezone::resolve($doctor);

        $patientZone = new DateTimeZone($patientTimezone);
        $doctorZone = new DateTimeZone($doctorTimezone);
        $clinicZone = new DateTimeZone($clinicTimezone);

        $selectedDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            (string) $values['from_date'],
            $patientZone
        );
        $dateErrors = DateTimeImmutable::getLastErrors();

        if (
            !$selectedDate
            || $selectedDate->format('Y-m-d') !== $values['from_date']
            || (
                $dateErrors !== false
                && ($dateErrors['warning_count'] || $dateErrors['error_count'])
            )
        ) {
            throw ValidationException::withMessages([
                'from_date' => 'Выберите корректную дату.',
            ]);
        }

        // The selected date belongs to the patient's local calendar.
        // Always show a complete ISO week: Monday through Sunday.
        $weekStart = $selectedDate->modify(
            '-' . ((int) $selectedDate->format('N') - 1) . ' days'
        );
        $weekEnd = $weekStart->modify('+7 days');

        $days = [];
        $monthlyLimits = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $patientDay = $weekStart->modify('+' . $offset . ' days');
            $date = $patientDay->format('Y-m-d');
            $labelDate = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $date,
                $patientTimezone
            );

            $days[$date] = [
                'date' => $date,
                'weekday' => mb_strtoupper(
                    $labelDate->locale('ru')->translatedFormat('D')
                ),
                'intervals' => [],
                'slots' => [],
                'message' => null,
                '_messages' => [],
            ];

            // Preload the monthly counter for each day shown to the patient.
            // This keeps the quota visible even when that day has no free slots.
            $patientNoon = $this->parseClinicTime(
                $date,
                '12:00',
                $patientTimezone
            );

            if ($patientNoon) {
                $clinicDateForPatientDay = $patientNoon
                    ->setTimezone($clinicZone)
                    ->format('Y-m-d');
                $quota = $monthlyLimit->usageForDate(
                    $patient,
                    $clinic,
                    $clinicDateForPatientDay
                );
                $monthlyLimits[$quota['month']] = $quota;
            }
        }

        /*
         * The existing schedule and booking rules use the clinic timezone.
         * Read the clinic dates intersecting the patient's local week, then
         * convert every offered instant to the doctor's and patient's zones.
         * The booking form posts the original clinic-local value, so the
         * existing validation and timestamp storage remain unchanged.
         */
        $clinicCursor = $weekStart
            ->setTimezone($clinicZone)
            ->setTime(0, 0)
            ->modify('-1 day');

        $clinicLast = $weekEnd
            ->setTimezone($clinicZone)
            ->setTime(0, 0)
            ->modify('+1 day');

        while ($clinicCursor <= $clinicLast) {
            $clinicDate = $clinicCursor->format('Y-m-d');

            $clinicDay = $availability->day(
                $doctor,
                $clinicId,
                $clinicDate,
                $duration,
                $format
            );

            // Keep only starts whose absolute instants fall into the patient's
            // currently displayed Monday-Sunday week. The surrounding clinic
            // dates are fetched only to cover timezone offsets at week edges.
            $clinicSlotsInPatientWeek = [];

            foreach ($clinicDay['slots'] as $candidateClock) {
                $candidateStart = $this->parseClinicTime(
                    $clinicDate,
                    (string) $candidateClock,
                    $clinicTimezone
                );

                if (!$candidateStart) {
                    continue;
                }

                $candidateForPatient = $candidateStart->setTimezone($patientZone);
                if ($candidateForPatient >= $weekStart && $candidateForPatient < $weekEnd) {
                    $clinicSlotsInPatientWeek[] = (string) $candidateClock;
                }
            }

            $clinicMonth = substr($clinicDate, 0, 7);
            if (!isset($monthlyLimits[$clinicMonth])) {
                $quota = $monthlyLimit->usageForDate(
                    $patient,
                    $clinic,
                    $clinicDate
                );
                $monthlyLimits[$quota['month']] = $quota;
            }
            $quota = $monthlyLimits[$clinicMonth];

            if ($quota['remaining'] <= 0 && $clinicSlotsInPatientWeek !== []) {
                $freeClinicSlots = [];
                $message = $quota['message'];
            } else {
                $freeClinicSlots = $clinicSlotsInPatientWeek !== []
                    ? $conflicts->filterAvailableSlots(
                        $doctor,
                        $patient,
                        $clinicDate,
                        $clinicTimezone,
                        $clinicSlotsInPatientWeek,
                        $duration
                    )
                    : [];

                $message = $clinicDay['message'];
                if (
                    $freeClinicSlots === []
                    && $clinicSlotsInPatientWeek !== []
                ) {
                    $message = 'Рабочее время есть, но все подходящие интервалы уже заняты.';
                }
            }

            if ($message !== null) {
                $messageInstant = null;

                if ($clinicSlotsInPatientWeek !== []) {
                    $messageInstant = $this->parseClinicTime(
                        $clinicDate,
                        (string) $clinicSlotsInPatientWeek[0],
                        $clinicTimezone
                    );
                }

                if (!$messageInstant) {
                    $messageInstant = $this->parseClinicTime(
                        $clinicDate,
                        '12:00',
                        $clinicTimezone
                    );
                }

                if ($messageInstant) {
                    $messageDate = $messageInstant
                        ->setTimezone($patientZone)
                        ->format('Y-m-d');

                    if (isset($days[$messageDate])) {
                        $days[$messageDate]['_messages'][] = $message;
                    }
                }
            }

            foreach ($clinicDay['intervals'] as $interval) {
                $clinicStart = $this->parseClinicTime(
                    $clinicDate,
                    (string) $interval['start'],
                    $clinicTimezone
                );
                $clinicEnd = $this->parseClinicTime(
                    $clinicDate,
                    (string) $interval['end'],
                    $clinicTimezone
                );

                if (!$clinicStart || !$clinicEnd || $clinicEnd <= $clinicStart) {
                    continue;
                }

                $patientStart = $clinicStart->setTimezone($patientZone);
                $patientEnd = $clinicEnd->setTimezone($patientZone);
                $patientDate = $patientStart->format('Y-m-d');

                if (!isset($days[$patientDate])) {
                    continue;
                }

                $doctorStart = $clinicStart->setTimezone($doctorZone);
                $doctorEnd = $clinicEnd->setTimezone($doctorZone);

                $intervalKey = $patientStart->format(DateTimeInterface::ATOM)
                    . '|'
                    . $doctorStart->format(DateTimeInterface::ATOM);

                $days[$patientDate]['intervals'][$intervalKey] = [
                    'patient_label' => $this->formatTimeRange(
                        $patientStart,
                        $patientEnd
                    ),
                    'doctor_label' => $this->formatTimeRange(
                        $doctorStart,
                        $doctorEnd
                    ),
                ];
            }

            foreach ($freeClinicSlots as $clock) {
                $clinicStart = $this->parseClinicTime(
                    $clinicDate,
                    (string) $clock,
                    $clinicTimezone
                );

                if (!$clinicStart) {
                    continue;
                }

                $utcStart = $clinicStart->setTimezone(
                    new DateTimeZone('UTC')
                );
                $patientStart = $utcStart->setTimezone($patientZone);

                if ($patientStart < $weekStart || $patientStart >= $weekEnd) {
                    continue;
                }

                $patientDate = $patientStart->format('Y-m-d');
                if (!isset($days[$patientDate])) {
                    continue;
                }

                $doctorStart = $utcStart->setTimezone($doctorZone);
                $slotKey = $utcStart->format('U');

                $days[$patientDate]['slots'][$slotKey] = [
                    'patient_date' => $patientStart->format('Y-m-d'),
                    'patient_time' => $patientStart->format('H:i'),
                    'doctor_date' => $doctorStart->format('Y-m-d'),
                    'doctor_time' => $doctorStart->format('H:i'),
                    'clinic_datetime' => $clinicDate . 'T' . (string) $clock,
                    'starts_at_utc' => $utcStart->format(DateTimeInterface::ATOM),
                ];
            }

            $clinicCursor = $clinicCursor->modify('+1 day');
        }

        foreach ($days as &$day) {
            $day['intervals'] = array_values($day['intervals']);
            $day['slots'] = array_values($day['slots']);

            usort(
                $day['slots'],
                static fn (array $left, array $right): int =>
                    strcmp($left['starts_at_utc'], $right['starts_at_utc'])
            );

            $messages = array_values(array_unique($day['_messages']));
            $day['message'] = $day['slots'] === []
                ? ($messages[0] ?? 'На эту дату нет свободных интервалов.')
                : null;

            unset($day['_messages']);
        }
        unset($day);

        return response()->json([
            'doctor' => [
                'id' => (int) $doctor->id,
                'name' => $this->doctorName($doctor),
                'speciality' => $this->doctorSpeciality($doctor),
            ],
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->modify('-1 day')->format('Y-m-d'),
            'patient_timezone' => $patientTimezone,
            'doctor_timezone' => $doctorTimezone,
            'clinic_timezone' => $clinicTimezone,
            'duration_minutes' => $duration,
            'monthly_limit' => PatientMonthlyAppointmentLimit::LIMIT,
            'monthly_limits' => array_values($monthlyLimits),
            'days' => array_values($days),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function store(
        Request $request,
        DoctorAppointmentAvailability $availability,
        RegistryAppointmentConflictService $conflicts,
        PatientMonthlyAppointmentLimit $monthlyLimit
    ): RedirectResponse {
        $values = $request->validate([
            'clinic_id' => ['required', 'integer', 'min:1'],
            'doctor_id' => ['required', 'integer', 'min:1'],
            'appointment_format' => ['required', 'in:in_person,telemedicine'],
            'appointment_datetime' => ['required', 'date_format:Y-m-d\TH:i'],
        ], [
            'appointment_datetime.required' => 'Выберите свободное время приёма.',
            'appointment_datetime.date_format' => 'Выберите дату и время из календаря.',
        ]);

        [$patient, $clinic] = $this->bookingContext($request);
        $clinicId = (int) $clinic->id;
        $doctorId = (int) $values['doctor_id'];
        $format = (string) $values['appointment_format'];
        $date = substr((string) $values['appointment_datetime'], 0, 10);
        $clock = substr((string) $values['appointment_datetime'], 11, 5);
        $duration = $this->defaultDuration();
        $start = ClinicAppointmentTime::parseLocal(
            (string) $values['appointment_datetime'],
            $clinicId
        );

        DB::transaction(function () use (
            $patient,
            $clinic,
            $clinicId,
            $doctorId,
            $format,
            $date,
            $clock,
            $duration,
            $start,
            $availability,
            $conflicts,
            $monthlyLimit
        ): void {
            $lockedPatient = Patient::query()
                ->lockForUpdate()
                ->findOrFail((int) $patient->id);

            if (PatientCareScope::isCurrentlyInpatient((int) $lockedPatient->id)) {
                throw ValidationException::withMessages([
                    'appointment_datetime' => 'Пациент сейчас находится в стационаре. Амбулаторная запись недоступна.',
                ]);
            }

            // The patient row is locked above: concurrent self-booking requests
            // for the same patient are serialized before checking the monthly cap.
            $monthlyLimit->assertCanBook(
                $lockedPatient,
                $clinic,
                $start
            );

            $lockedDoctor = Doctor::query()
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->findOrFail($doctorId);

            $this->assertDoctorCanBook($lockedDoctor, $clinic, $format);

            $day = $availability->day(
                $lockedDoctor,
                $clinicId,
                $date,
                $duration,
                $format
            );
            $freeSlots = $conflicts->filterAvailableSlots(
                $lockedDoctor,
                $lockedPatient,
                $date,
                $day['timezone'],
                $day['slots'],
                $duration
            );

            if (!in_array($clock, $freeSlots, true)) {
                throw ValidationException::withMessages([
                    'appointment_datetime' => 'Это время уже недоступно. Обновите календарь и выберите другой интервал.',
                ]);
            }

            $availability->assert(
                $lockedDoctor,
                $clinicId,
                $start,
                $duration,
                $format
            );
            $conflicts->assertNoOverlap(
                $lockedDoctor,
                $lockedPatient,
                $start,
                $duration
            );

            $record = new RegistryRecord();
            $record->patient_id = (int) $lockedPatient->id;
            $record->doctor_id = (int) $lockedDoctor->id;
            $record->clinic_id = $clinicId;
            $record->appointment_format = $format;
            $record->appointment = false;
            $record->setAttribute('appointment_duration_minutes', $duration);

            ClinicAppointmentTime::stamp($record, $start, $clinicId);
            $record->save();
        }, 3);

        return redirect()
            ->route('patients.calendar')
            ->with('success', 'Вы успешно записаны на приём. Информация о записи появилась в календаре.');
    }

    private function bookingContext(Request $request): array
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        $patient = $user->patient()->firstOrFail();

        if (PatientCareScope::isCurrentlyInpatient((int) $patient->id)) {
            throw ValidationException::withMessages([
                'appointment_datetime' => 'Во время госпитализации амбулаторная запись недоступна.',
            ]);
        }

        $clinicId = (int) $request->integer('clinic_id');

        if ($clinicId <= 0) {
            throw ValidationException::withMessages([
                'clinic_id' => 'Сначала найдите и выберите амбулаторную клинику.',
            ]);
        }

        $clinic = Clinic::query()
            ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('deleted_at')
            ->find($clinicId);

        if (!$clinic) {
            throw ValidationException::withMessages([
                'clinic_id' => 'Клиника не найдена или не принимает амбулаторные записи.',
            ]);
        }

        return [$patient, $clinic];
    }

    private function doctorOptions(Clinic $clinic, ?int $assignedDoctorId = null): array
    {
        $clinicFormats = $this->clinicAppointmentFormats($clinic);
        $telemedicineEnabled = ClinicTelemedicineSettings::enabled($clinic);
        $telemedicineEligibility = app(DoctorTelemedicineEligibility::class);

        return Doctor::query()
            ->where('clinic_id', (int) $clinic->id)
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->get()
            ->map(function (Doctor $doctor) use (
                $clinic,
                $clinicFormats,
                $telemedicineEnabled,
                $telemedicineEligibility,
                $assignedDoctorId
            ): ?array {
                $formats = [];

                if (
                    in_array('in_person', $clinicFormats, true)
                    && $this->hasActiveDoctorCabinet($doctor, (int) $clinic->id)
                ) {
                    $formats[] = 'in_person';
                }

                if (
                    in_array('telemedicine', $clinicFormats, true)
                    && $telemedicineEnabled
                    && $telemedicineEligibility->allows($doctor)
                ) {
                    $formats[] = 'telemedicine';
                }

                if ($formats === []) {
                    return null;
                }

                return [
                    'id' => (int) $doctor->id,
                    'name' => $this->doctorName($doctor),
                    'speciality' => $this->doctorSpeciality($doctor),
                    'formats' => $formats,
                    'assigned' => (int) $doctor->id === (int) $assignedDoctorId,
                ];
            })
            ->filter()
            ->sortBy([
                ['assigned', 'desc'],
                ['name', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function clinicAppointmentFormats(Clinic $clinic): array
    {
        $services = $clinic->getRawOriginal('services');

        if ($services === null || $services === '') {
            return ['in_person', 'telemedicine'];
        }

        if (is_string($services)) {
            $services = json_decode($services, true);
        }

        if (!is_array($services)) {
            return [];
        }

        return collect($services)
            ->map(static fn ($value): string => (string) $value)
            ->filter(static fn (string $value): bool =>
                in_array($value, ['in_person', 'telemedicine'], true)
            )
            ->unique()
            ->values()
            ->all();
    }

    private function hasActiveDoctorCabinet(Doctor $doctor, int $clinicId): bool
    {
        return ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->where('cabinet_type', ClinicCabinet::TYPE_DOCTOR)
            ->where(static function ($query) use ($doctor): void {
                $query->where('doctor_id', (int) $doctor->id)
                    ->orWhere('replace_doctor_id', (int) $doctor->id);
            })
            ->exists();
    }

    private function assertDoctorCanBook(Doctor $doctor, Clinic $clinic, string $format): void
    {
        if ((int) $doctor->clinic_id !== (int) $clinic->id) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Выбранный врач не относится к клинике медицинской карты.',
            ]);
        }

        if (!in_array($format, $this->clinicAppointmentFormats($clinic), true)) {
            throw ValidationException::withMessages([
                'appointment_format' => 'Выбранный формат приёма отключён в настройках клиники.',
            ]);
        }

        if ($format === 'in_person') {
            if (!$this->hasActiveDoctorCabinet($doctor, (int) $clinic->id)) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'У выбранного врача нет активного кабинета для очного приёма.',
                ]);
            }

            return;
        }

        if (!ClinicTelemedicineSettings::enabled($clinic)) {
            throw ValidationException::withMessages([
                'appointment_format' => 'Телемедицина не подключена в этой клинике.',
            ]);
        }

        app(DoctorTelemedicineEligibility::class)->assertAllowed($doctor);
    }

    private function normalizePatientTimezone(string $timezone): string
    {
        $timezone = trim($timezone);

        if (
            $timezone === ''
            || !in_array(
                $timezone,
                DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'patient_timezone' => 'Не удалось определить часовой пояс устройства. Обновите страницу и попробуйте снова.',
            ]);
        }

        return $timezone;
    }

    private function parseClinicTime(
        string $date,
        string $clock,
        string $timezone
    ): ?DateTimeImmutable {
        if ($clock === '24:00') {
            try {
                return ClinicAppointmentTime::parseLocalInTimezone(
                    $date . 'T00:00',
                    $timezone
                )->modify('+1 day');
            } catch (ValidationException) {
                return null;
            }
        }

        try {
            return ClinicAppointmentTime::parseLocalInTimezone(
                $date . 'T' . $clock,
                $timezone
            );
        } catch (ValidationException) {
            return null;
        }
    }

    private function formatTimeRange(
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ): string {
        $endLabel = $end->format('H:i');

        if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
            $endLabel .= ' · ' . $end->format('d.m');
        }

        return $start->format('H:i') . '–' . $endLabel;
    }

    private function upcomingAppointments(Patient $patient): array
    {
        $query = RegistryRecord::query()
            ->with('doctor')
            ->where('patient_id', (int) $patient->id)
            ->whereNull('deleted_at');

        if (Schema::hasColumn('registry_records', 'appointment_starts_at_utc')) {
            ClinicAppointmentTime::whereComparedToNow($query, '>');
            ClinicAppointmentTime::orderByStart($query, 'asc');
        } else {
            $query->where('for_datetime', '>=', now())
                ->orderBy('for_datetime');
        }

        $records = $query->limit(8)->get();
        $clinicNames = Clinic::query()
            ->whereIn(
                'id',
                $records->pluck('clinic_id')->filter()->unique()->values()->all()
            )
            ->pluck('name', 'id');

        return $records
            ->map(function (RegistryRecord $record) use ($clinicNames): array {
                $instant = ClinicAppointmentTime::startInstant($record);
                $doctorTimezone = $record->doctor
                    ? DoctorAppointmentTimezone::resolve($record->doctor)
                    : ClinicAppointmentTime::timezone(
                        $record->clinic_id === null
                            ? null
                            : (int) $record->clinic_id
                    );
                $doctorStart = $instant
                    ? $instant->setTimezone(new DateTimeZone($doctorTimezone))
                    : null;

                return [
                    'date' => $doctorStart?->format('d.m.Y') ?? '—',
                    'time' => $doctorStart?->format('H:i') ?? '—',
                    'doctor_timezone' => $doctorTimezone,
                    'starts_at_utc' => $instant?->format(DateTimeInterface::ATOM),
                    'timezone_known' => $instant !== null,
                    'doctor' => $record->doctor
                        ? $this->doctorName($record->doctor)
                        : 'Врач не указан',
                    'clinic' => $record->clinic_id
                        ? (string) (
                            $clinicNames->get((int) $record->clinic_id)
                            ?: 'Клиника #' . $record->clinic_id
                        )
                        : 'Клиника не указана',
                    'format' => $record->appointment_format === 'telemedicine'
                        ? 'Телемедицина'
                        : 'Очный приём',
                ];
            })
            ->all();
    }

    private function doctorName(Doctor $doctor): string
    {
        $name = trim(implode(' ', array_filter([
            $doctor->surname,
            $doctor->name,
            $doctor->patronym,
        ])));

        return $name !== '' ? $name : 'Врач #' . $doctor->id;
    }

    private function doctorSpeciality(Doctor $doctor): string
    {
        $status = trim((string) ($doctor->status ?? ''));

        if ($status !== '') {
            $speciality = collect(
                preg_split('/\s*[,;|]\s*/u', $status, -1, PREG_SPLIT_NO_EMPTY) ?: []
            )
                ->map(static fn ($value): string => trim((string) $value))
                ->filter()
                ->reject(static fn (string $value): bool =>
                    mb_strtolower($value) === 'врач'
                )
                ->unique()
                ->implode(', ');

            if ($speciality !== '') {
                return $speciality;
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

    private function defaultDuration(): int
    {
        $duration = (int) config('doctor_appointment_availability.default_duration_minutes', 30);
        return $duration >= 5 && $duration <= 480 ? $duration : 30;
    }
}
