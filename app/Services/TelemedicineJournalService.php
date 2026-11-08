<?php

namespace App\Services;

use App\Http\Controllers\Doctors\ReceptionController;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\HospitalPatientAccess;
use App\Support\TelemedicineJournalRules;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Manages the appointments displayed in the existing telemedicine journal. */
class TelemedicineJournalService
{
    private const CLINICAL_FIELDS = [
        'complaints', 'disease_history', 'life_history', 'social_history',
        'mental_status', 'diagnosis_code', 'diagnosis_title', 'conclusion',
        'recommendations', 'service_code', 'service_name',
    ];
    private const CONNECTION_EVENTS = [
        'participant_joined', 'participant_left', 'media_connected',
        'connection_failed', 'call_ended',
    ];

    public function __construct(
        private HospitalPatientAccess $access,
        private MedicalVisitAuditService $audit
    ) {}

    public function ready(): bool
    {
        foreach (['registry_records', 'medical_visit_events', 'medical_visit_confirmations'] as $table) {
            if (!Schema::hasTable($table)) return false;
        }
        return Schema::hasColumn('registry_records', 'appointment_format')
            && Schema::hasColumn('registry_records', 'deleted_at');
    }

    public function canManage($user, Patient $patient): bool
    {
        // NORHNAIM_TELEMED_ACCESS_V1: separate scheduling permission, not clinical editing.
        return app(\App\Support\TelemedicineJournalAccess::class)->canManage($user, $patient);
    }

    public function journalAppointments($user, Patient $patient): \Illuminate\Database\Eloquent\Builder
    {
        $policy = app(\App\Support\TelemedicineJournalAccess::class);
        if ($policy->canManage($user, $patient)) {
            return $policy->manageableAppointments($user, $patient);
        }
        return $this->audit->visibleAppointments($user, $patient, $this->access);
    }


    public function authorize($user, Patient $patient): void
    {
        abort_unless($this->canManage($user, $patient), 403,
            app(\App\Support\TelemedicineJournalAccess::class)->explain($user, $patient)['reason']);
        abort_unless($this->ready(), 503, 'Не установлены таблицы журнала телемедицины.');
    }

    /** A patient in private practice is not grouped with unrelated private doctors. */
    private function doctorQuery(Patient $patient)
    {
        $patient->loadMissing('doctor');
        $assigned = $patient->doctor;
        $query = Doctor::query()->with('clinic');
        if (!$assigned) return $query->whereRaw('1 = 0');
        $clinicId = (int) ($assigned->clinic_id ?? 0);
        $patientClinicId = (int) ($patient->getAttribute('clinic_id') ?? 0);
        if ($patientClinicId > 0 && $patientClinicId !== $clinicId) {
            return $query->whereRaw('1 = 0');
        }
        return $clinicId > 0
            ? $query->where('clinic_id', $clinicId)
            : $query->whereKey($assigned->getKey());
    }

    private function doctorName(Doctor $doctor): string
    {
        return trim(implode(' ', array_filter([
            $doctor->surname, $doctor->name, $doctor->patronym,
        ]))) ?: 'Врач #' . $doctor->getKey();
    }

    /** Additional data for Blade. No database queries or authorization in templates. */
    public function decorate(Request $request, Patient $patient, array $auditContext): array
    {
        $ready = $this->ready();
        $canManage = $ready && $this->canManage($request->user(), $patient);
        $doctors = $canManage
            ? $this->doctorQuery($patient)->orderBy('surname')->orderBy('name')->get()
            : collect();
// clinic-telemedicine-timezones:timezone-journal-form-context:v1:begin
$oldDoctor = $request->old('telemed_doctor_id', $patient->doctor_id);
        $selected = is_scalar($oldDoctor) ? (int) $oldDoctor : (int) $patient->doctor_id;
        $selectedDoctor = $doctors->firstWhere('id', $selected) ?? $doctors->first();
        $doctorTimezone = \App\Support\DoctorAppointmentTimezone::resolve(
            $selectedDoctor
        );
        $defaultDate = now($doctorTimezone)->addMinutes(30)->startOfMinute()->format('Y-m-d\TH:i');
        $startsAt = $request->old('telemed_starts_at', $defaultDate);
        $startsAt = is_string($startsAt) ? $startsAt : '';
        $duration = $request->old('telemed_duration_minutes', config('doctor_appointment_availability.default_duration_minutes', 30));
        $duration = is_scalar($duration) ? (string) $duration : '';
        $requestId = $request->old('telemed_request_id', (string) Str::uuid());
        $requestId = is_string($requestId) ? $requestId : '';
// clinic-telemedicine-timezones:timezone-journal-form-context:v1:end
        $query = $request->query();
        unset($query['telemed_page'], $query['telemed_open']);
        $query['tab'] = 'telemedicine-log';
        $activeUrl = $request->url() . '?' . http_build_query(array_merge($query, ['telemed_archive' => 0]));
        $archiveUrl = $request->url() . '?' . http_build_query(array_merge($query, ['telemed_archive' => 1]));
        $auditContext['telemedManager'] = [
            'ready' => $ready,
            'can_create' => $canManage && $doctors->isNotEmpty(),
            'disabled_reason' => !$ready ? 'Сначала установите таблицы журнала.'
                : (!$canManage ? app(\App\Support\TelemedicineJournalAccess::class)->explain($request->user(), $patient)['reason']
                    : ($doctors->isEmpty() ? 'У пациента не определён лечащий врач или клиника.' : '')),
            'doctors' => $doctors->map(fn (Doctor $doctor) => [
                'id' => (int) $doctor->getKey(), 'name' => $this->doctorName($doctor),
                'selected' => (int) $doctor->getKey() === $selected,
            ])->all(),
            'create_url' => route('doctors.patients.telemedicine-journal.store', $patient),
// clinic-telemedicine-timezones:timezone-journal-form-values:v1:begin
'starts_at' => $startsAt,
            'duration_minutes' => $duration,
// clinic-telemedicine-timezones:timezone-journal-form-values:v1:end
// clinic-telemedicine-timezones:timezone-journal-form-minimum:v1:begin
'minimum' => now($doctorTimezone)->startOfMinute()->format('Y-m-d\TH:i'),
// clinic-telemedicine-timezones:timezone-journal-form-minimum:v1:end
// clinic-telemedicine-timezones:timezone-journal-form-zone:v1:begin
'timezone' => $doctorTimezone,
// clinic-telemedicine-timezones:timezone-journal-form-zone:v1:end
// clinic-telemedicine-timezones:timezone-journal-form-request-id:v1:begin
'request_id' => $requestId,
// clinic-telemedicine-timezones:timezone-journal-form-request-id:v1:end
            'create_errors' => $request->session()->get('errors')?->getBag('telemedCreate')->all() ?? [],
            'delete_errors' => $request->session()->get('errors')?->getBag('telemedDelete')->all() ?? [],
            'success' => $request->session()->get('telemed_journal_success'),
            'active_url' => $activeUrl, 'archive_url' => $archiveUrl,
            'show_archive' => $request->boolean('telemed_archive'),
        ];
        $log = $auditContext['telemedicineLog'] ?? null;
        if (!$ready || !$log || $log->getCollection()->isEmpty()) return $auditContext;

        $ids = $log->getCollection()->pluck('id')->all();
        // Reuse the established appointment visibility scope, including for writes.
        $records = $this->journalAppointments($request->user(), $patient)
            ->whereKey($ids)->get()->keyBy('id');
        $snapshots = $this->snapshots($ids);
        $log->setCollection($log->getCollection()->map(function (array $call) use (
            $records, $snapshots, $canManage, $patient
        ): array {
            $record = $records->get($call['id']);
            if (!$record) {
                $call['journal'] = ['can_delete' => false, 'label' => 'Недоступен',
                    'state' => 'unknown', 'reason' => 'Нет доступа к записи.', 'delete_url' => null];
                return $call;
            }
            $call['journal'] = $this->decision($record, $snapshots, $canManage);
            $call['journal']['delete_url'] = route('doctors.patients.telemedicine-journal.destroy', [
                'patient' => $patient->getKey(), 'registryRecord' => $record->getKey(),
            ]);
            return $call;
        }));
        return $auditContext;
    }

    private function snapshots(array $ids, bool $lock = false): array
    {
        $confirm = DB::table('medical_visit_confirmations')->whereIn('registry_record_id', $ids);
        $encounters = Schema::hasTable('medical_encounters')
            ? DB::table('medical_encounters')->whereIn('registry_record_id', $ids) : null;
        if ($lock) {
            $confirm->lockForUpdate();
            $encounters?->lockForUpdate();
        }
        return [
            'confirmations' => $confirm->get()->keyBy('registry_record_id'),
            'encounters' => $encounters?->get()->groupBy('registry_record_id') ?? collect(),
            'events' => DB::table('medical_visit_events')->whereIn('registry_record_id', $ids)
                ->where('source', 'signaling')->whereIn('event_type', self::CONNECTION_EVENTS)
                ->orderBy('occurred_at')->orderBy('id')
                ->get(['registry_record_id', 'room_id', 'event_type', 'participant_role'])
                ->groupBy('registry_record_id'),
        ];
    }

    private function decision(RegistryRecord $record, array $snapshots, bool $canManage): array
    {
        $id = (int) $record->getKey();
        $confirmation = $snapshots['confirmations']->get($id)?->outcome;
        $encounters = $snapshots['encounters']->get($id, collect());
        $consultation = null;
        $document = false;
        foreach ($encounters as $encounter) {
            $attributes = (array) $encounter;
            $status = $attributes['consultation_status'] ?? null;
            // Multiple unexpected clinical rows must not hide a completed record.
            if ($status === 'completed') $document = true;
            if ($status === 'in_progress' || $consultation !== 'in_progress') $consultation = $status;
            foreach (self::CLINICAL_FIELDS as $field) {
                if (isset($attributes[$field]) && trim((string) $attributes[$field]) !== '') $document = true;
            }
            foreach (['signed_at', 'finalized_at', 'completed_at'] as $field) {
                if (!empty($attributes[$field])) $document = true;
            }
        }
// clinic-telemedicine-timezones:timezone-journal-deletion-clock:v1:begin
$start = \App\Support\ClinicAppointmentTime::startInstant($record)
            ?? \App\Support\ClinicAppointmentTime::legacyLocal($record);
// clinic-telemedicine-timezones:timezone-journal-deletion-clock:v1:end
        return TelemedicineJournalRules::deletion(
            $canManage, $record->deleted_at !== null, $start, $confirmation,
            $consultation, $document,
            TelemedicineJournalRules::hasOpenConnection($snapshots['events']->get($id, collect())), now()
        );
    }

    public function create($user, Patient $patient, array $data): RegistryRecord
    {
        $this->authorize($user, $patient);
        $eventUid = 'tm-schedule-' . strtolower($data['telemed_request_id']);
        $duration = (int) $data['telemed_duration_minutes'];
        return DB::transaction(function () use ($user, $patient, $data, $duration, $eventUid) {
            $lockedPatient = Patient::query()->whereKey($patient->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize($user, $lockedPatient);
            $doctor = $this->doctorQuery($lockedPatient)->whereKey($data['telemed_doctor_id'])
                ->lockForUpdate()->first();
            if (!$doctor) $this->invalid('Выбранный врач недоступен в клинике пациента.', 'telemed_doctor_id');
            $clinicId = max(0, (int) $doctor->clinic_id);
            $doctorTimezone = \App\Support\DoctorAppointmentTimezone::resolve(
                $doctor
            );
            $start = Carbon::instance(
                \App\Support\ClinicAppointmentTime::parseLocalInTimezone(
                    (string) $data['telemed_starts_at'],
                    $doctorTimezone
                )
            );
            $utc = $start->copy()->setTimezone('UTC');
            $previous = DB::table('medical_visit_events')->where('event_uid', $eventUid)->first();
            if ($previous) {
                $meta = json_decode((string) $previous->details, true) ?: [];
                abort_unless((int) $previous->patient_id === (int) $patient->getKey()
                    && (int) $previous->actor_user_id === (int) $user->getKey()
                    && $previous->event_type === 'session_scheduled'
                    && (int) ($meta['doctor_id'] ?? 0) === (int) $data['telemed_doctor_id']
                    && ($meta['starts_at_utc'] ?? null) === $utc->format(\DateTimeInterface::ATOM)
                    && ($meta['appointment_timezone'] ?? null) === $start->getTimezone()->getName()
                    && (int) ($meta['duration_minutes'] ?? 0) === $duration, 409,
                    'Этот запрос уже использован с другими параметрами. Обновите страницу.');
                return $this->journalAppointments($user, $lockedPatient)
                    ->whereKey($previous->registry_record_id)->firstOrFail();
            }
            if (!$start->isFuture()) $this->invalid('Укажите дату и время позже текущего момента.', 'telemed_starts_at');
            if ($clinicId > 0) {
                if (!$doctor->clinic || !\App\Services\ClinicTelemedicineSettings::enabled($doctor->clinic)) {
                    $this->invalid('Телемедицинский формат отключён в настройках клиники.', 'telemed_starts_at');
                }
                // This path previously checked only the clinic opening time.
                // Apply the same full doctor shift, vacation and exception check.
                app(DoctorAppointmentAvailability::class)->assert($doctor, $clinicId, $start, $duration, 'telemedicine');
            }
            app(ReceptionController::class)->validateAppointmentWithinClinicSchedule($doctor, $start, appointmentFormat: 'telemedicine');
// NORHNAIM_TELEMED_AVAILABLE_SLOTS_V1:
// тот же overlap-контроль, что используется при показе свободных окон.
app(\App\Services\TelemedicineAvailableSlotService::class)
    ->assertFree(
        $doctor,
        $lockedPatient,
        $start,
        $duration
    );

$record = new RegistryRecord();
$record->patient_id = $lockedPatient->getKey();
$record->doctor_id = $doctor->getKey();
$record->clinic_id = $doctor->clinic_id;
\App\Support\ClinicAppointmentTime::stamp(
    $record,
    $start,
    $clinicId
,
                $doctorTimezone
            );
            $record->appointment_format = 'telemedicine';
            $record->appointment = false;
            $record->setAttribute('appointment_duration_minutes', $duration);
            $record->save();
            $this->audit->appendEvent((int) $record->getKey(), (int) $lockedPatient->getKey(),
                'session_scheduled', 'staff', 'system', (int) $user->getKey(), null,
                ['doctor_id' => (int) $doctor->getKey(), 'starts_at' => $start->format('Y-m-d H:i:s'),
                    'starts_at_utc' => $utc->format(\DateTimeInterface::ATOM),
                    'appointment_timezone' => $start->getTimezone()->getName(), 'duration_minutes' => $duration],
                now(), $eventUid);
            return $record;
        }, 3);
    }

    public function cancel($user, Patient $patient, int $appointmentId, string $reason): void
    {
        $this->authorize($user, $patient);
        DB::transaction(function () use ($user, $patient, $appointmentId, $reason): void {
            // Lock current assignment before selecting a cancellable appointment.
            $freshPatient = Patient::query()->whereKey($patient->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize($user, $freshPatient);
            $record = $this->journalAppointments($user, $freshPatient)
                ->whereKey($appointmentId)->where('appointment_format', 'telemedicine')
                ->lockForUpdate()->firstOrFail();
            if ($record->deleted_at !== null) return; // Idempotent repeated DELETE.
            $decision = $this->decision($record, $this->snapshots([$appointmentId], true), true);
            if (!$decision['can_delete']) $this->invalid($decision['reason'], 'telemed_delete_reason', 'telemedDelete');
            // Explicit soft cancellation. Never forceDelete or delete clinical/audit rows.
            DB::table('registry_records')->where('id', $appointmentId)->whereNull('deleted_at')->update([
                'deleted_at' => now(), 'updated_at' => now(),
            ]);
            $this->audit->appendEvent($appointmentId, (int) $patient->getKey(),
                'session_cancelled', 'staff', 'system', (int) $user->getKey(), null,
                ['reason' => $reason, 'previous_state' => $decision['state']], now());
        }, 3);
    }

    private function invalid(string $message, string $field, string $bag = 'telemedCreate'): never
    {
        throw ValidationException::withMessages([$field => $message])->errorBag($bag);
    }
}
