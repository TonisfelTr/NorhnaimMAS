<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\ClinicAppointmentTime;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** One provider snapshot per scheduled appointment; GET never prepares a conference. */
final class TelemedicineProviderSessionService
{
    private const TABLE = 'telemedicine_provider_sessions';

    public function __construct(
        private TelemedicineJournalService $journal,
        private MedicalVisitAuditService $audit,
        private TelemedicineProviderTransport $transport,
        private HospitalPatientAccess $clinicalAccess
    ) {}

    public function ready(): bool
    {
        return Schema::hasTable(self::TABLE) && $this->journal->ready();
    }

    private function scopedRecord($user, Patient $patient, int $recordId, bool $lock = false): RegistryRecord
    {
        $this->journal->authorize($user, $patient);
        abort_unless($this->ready(), 503, 'Сначала выполните миграции настроек телемедицины.');
        $query = $this->journal->journalAppointments($user, $patient)
            ->whereKey($recordId)->where('appointment_format', 'telemedicine');
        if ($lock) $query->lockForUpdate();
        return $query->firstOrFail();
    }

    private function clinic(RegistryRecord $record): Clinic
    {
        $id = (int) $record->getAttribute('clinic_id');
        abort_unless($id > 0, 409, 'У записи не определена клиника.');
        // The clinic comes only from the persisted, authorized appointment.
        return Clinic::query()->whereKey($id)->firstOrFail();
    }

    /** Participation is narrower than management and never grants patient editing. */
    private function participationRecord($user, Patient $patient, int $recordId, bool $lock = false): RegistryRecord
    {
        if ($this->journal->canManage($user, $patient)) return $this->scopedRecord($user, $patient, $recordId, $lock);
        abort_unless($user && $user->hasRole('doctors') && $this->clinicalAccess->canRead($user, $patient), 403);
        abort_unless($this->ready(), 503, 'Сначала выполните миграции настроек телемедицины.');
        $query = RegistryRecord::query()->withTrashed()->whereKey($recordId)
            ->where('patient_id', $patient->getKey())->where('appointment_format', 'telemedicine');
        if ($lock) $query->lockForUpdate();
        $record = $query->firstOrFail();
        abort_unless($this->isAssignedDoctor($user, $record), 403, 'Вы не назначены врачом этого приёма.');
        return $record;
    }

    private function sessionFor(RegistryRecord $record, bool $lock = false): ?object
    {
        $query = DB::table(self::TABLE)->where('registry_record_id', $record->getKey());
        if ($lock) $query->lockForUpdate();
        return $query->first();
    }

    private function window(RegistryRecord $record): ?array
    {
        $start = ClinicAppointmentTime::startInstant($record);
        if (!$start) return null;
        $before = max(0, min(240, (int) config('clinic_telemedicine.join_before_minutes', 15)));
        $after = max(1, min(1440, (int) config('clinic_telemedicine.join_after_minutes', 120)));
        return [
            'start' => $start->getTimestamp(),
            'opens' => $start->getTimestamp() - $before * 60,
            'closes' => $start->getTimestamp() + $after * 60,
            'before_minutes' => $before,
            'after_minutes' => $after,
        ];
    }

    private function closedReason(RegistryRecord $record, Clinic $clinic, ?object $session = null): ?string
    {
        if ($record->getAttribute('deleted_at') !== null) return 'Запись на приём отменена.';
        if ($record->getAttribute('appointment_format') !== 'telemedicine') return 'У записи изменён формат приёма.';
        if (($clinic->getAttributes()['deleted_at'] ?? null) !== null) return 'Клиника недоступна.';
        if (!ClinicTelemedicineSettings::enabled($clinic)) return 'В клинике не включён формат «Телемедицина».';
        if (!Doctor::query()->whereKey($record->doctor_id)->where('clinic_id', $clinic->getKey())->exists()) {
            return 'Назначенный на приём врач больше не доступен в этой клинике.';
        }
        if ($session && ((int) $session->registry_record_id !== (int) $record->getKey()
            || (int) $session->patient_id !== (int) $record->patient_id
            || (int) $session->clinic_id !== (int) $record->clinic_id
            || (int) $session->doctor_id !== (int) $record->doctor_id)) {
            return 'У записи изменились участники или клиника. Старое приглашение больше не действует.';
        }
        if (DB::table('medical_visit_confirmations')->where('registry_record_id', $record->getKey())
            ->whereIn('outcome', ['held', 'not_held'])->exists()) {
            return 'Результат приёма уже подтверждён. Подключение закрыто.';
        }
        if (Schema::hasTable('medical_encounters')
            && DB::table('medical_encounters')->where('registry_record_id', $record->getKey())
                ->whereIn('consultation_status', ['completed', 'not_held'])->exists()) {
            return 'Консультация уже завершена. Подключение закрыто.';
        }
        $window = $this->window($record);
        if (!$window) return 'Для этой записи ещё не подтверждён часовой пояс. Откройте редактирование приёма и сохраните время с подтверждённым часовым поясом.';
        if (time() >= $window['closes'] - 60) return 'Окно подключения к этому приёму завершено.';
        return null;
    }

    private function assertOpen(RegistryRecord $record, Clinic $clinic, ?object $session = null): void
    {
        $reason = $this->closedReason($record, $clinic, $session);
        if ($reason !== null) $this->invalid($reason);
    }

    private function assertWithinWindow(RegistryRecord $record): array
    {
        $window = $this->window($record);
        if (!$window || time() < $window['opens'] || time() >= $window['closes'] - 60) {
            $this->invalid('Подключение доступно только в указанное на странице время приёма.');
        }
        return $window;
    }

    /** Reserves the local session BEFORE the single external HTTP request. */
    public function prepare($user, Patient $patient, int $recordId, #[\SensitiveParameter] ?string $manualUrl): void
    {
        $reservation = DB::transaction(function () use ($user, $patient, $recordId, $manualUrl): array {
            $record = $this->scopedRecord($user, $patient, $recordId, true);
            $clinic = $this->clinic($record);
            $existing = $this->sessionFor($record, true);
            $this->assertOpen($record, $clinic, $existing);
            // Repeated POST cannot silently change provider or create another remote meeting.
            if ($existing) return ['create_api' => false];
            $profile = ClinicTelemedicineSettings::profile($clinic);
            if ($profile['settings_errors'] !== []) $this->invalid(implode(' ', $profile['settings_errors']));
            $provider = $profile['provider'];
            $mode = $provider === 'webrtc' ? 'self_hosted' : $profile['telemost_mode'];
            $ready = $provider === 'webrtc' || $mode === 'manual';
            $url = null;
            $page = null;
            $room = null;
            $token = '';
            try {
                if ($provider === 'webrtc') {
                    $page = TelemedicineProviderTransport::webrtcPage($profile['webrtc_url']);
                    TelemedicineProviderTransport::signingKey(ClinicTelemedicineSettings::signalingSecret($clinic));
                    $room = 'visit_' . bin2hex(random_bytes(24));
                } elseif ($provider === 'telemost' && $mode === 'manual') {
                    $url = TelemedicineProviderTransport::telemostJoinUrl($manualUrl ?? '');
                    $this->assertUniqueUrl($url);
                } elseif ($provider === 'telemost' && $mode === 'api') {
                    $token = ClinicTelemedicineSettings::telemostToken($clinic);
                    if ($token === '') $this->invalid('Сначала сохраните OAuth-токен Телемоста в настройках клиники.');
                } else {
                    $this->invalid('Проверьте выбранный способ проведения консультаций.');
                }
            } catch (\InvalidArgumentException $e) {
                $this->invalid($e->getMessage(), $mode === 'manual' ? 'manual_join_url' : 'telemedicine');
            }
            $publicId = (string) Str::uuid();
            $sessionId = DB::table(self::TABLE)->insertGetId([
                'public_id' => $publicId,
                'registry_record_id' => (int) $record->getKey(),
                'patient_id' => (int) $record->patient_id,
                'clinic_id' => (int) $clinic->getKey(),
                'doctor_id' => (int) $record->doctor_id,
                'provider' => $provider, 'mode' => $mode,
                'status' => $ready ? 'ready' : 'preparing',
                'preparation_started_epoch' => time(),
                'room_id' => $room, 'webrtc_url' => $page,
                'join_url_ciphertext' => $url === null ? null : Crypt::encryptString($url),
                'join_url_hash' => $url === null ? null : TelemedicineProviderTransport::telemostUrlHash($url),
                'created_by_user_id' => (int) $user->getKey(),
                'prepared_at' => $ready ? now() : null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->event($record, $ready ? 'provider_prepared' : 'provider_creation_requested', $user,
                $provider, $publicId, $room);
            return ['create_api' => !$ready, 'id' => $sessionId, 'token' => $token];
        }, 3);
        if (!$reservation['create_api']) return;

        try {
            $remote = $this->transport->createTelemost($reservation['token']);
            DB::transaction(function () use ($reservation, $remote, $user, $patient, $recordId): void {
                // Keep the appointment -> provider session lock order used by prepare/cancel.
                $record = $this->scopedRecord($user, $patient, $recordId, true);
                $session = $this->sessionFor($record, true);
                if (!$session || (int) $session->id !== (int) $reservation['id'] || $session->status !== 'preparing') return;
                $this->assertUniqueUrl($remote['join_url'], (int) $session->id);
                DB::table(self::TABLE)->where('id', $session->id)->update([
                    'status' => 'ready', 'telemost_conference_id' => $remote['id'],
                    'join_url_ciphertext' => Crypt::encryptString($remote['join_url']),
                    'join_url_hash' => TelemedicineProviderTransport::telemostUrlHash($remote['join_url']),
                    'prepared_at' => now(), 'updated_at' => now(), 'last_error_code' => null,
                ]);
                // A cancellation during HTTP is preserved; every subsequent join checks it.
                $this->event($record, 'provider_prepared', $user, 'telemost', $session->public_id);
            }, 3);
        } catch (\Throwable) {
            // Even a timeout may have created a remote meeting. Never retry it automatically.
            DB::table(self::TABLE)->where('id', $reservation['id'])->where('status', 'preparing')->update([
                'status' => 'failed', 'last_error_code' => 'api_result_unconfirmed', 'updated_at' => now(),
            ]);
            $session = DB::table(self::TABLE)->where('id', $reservation['id'])->first();
            if ($session && $session->status === 'failed') {
                $this->audit->appendEvent((int) $session->registry_record_id, (int) $session->patient_id,
                    'provider_creation_unconfirmed', 'system', 'system', (int) $user->getKey(), null,
                    ['provider' => 'telemost', 'session_id' => $session->public_id]);
            }
        }
    }

    /** Explicit recovery attaches an existing meeting; it never repeats the create API call. */
    public function attachManual($user, Patient $patient, int $recordId, #[\SensitiveParameter] string $manualUrl): void
    {
        try { $url = TelemedicineProviderTransport::telemostJoinUrl($manualUrl); }
        catch (\InvalidArgumentException $e) { $this->invalid($e->getMessage(), 'manual_join_url'); }
        DB::transaction(function () use ($user, $patient, $recordId, $url): void {
            $record = $this->scopedRecord($user, $patient, $recordId, true);
            $clinic = $this->clinic($record);
            $session = $this->sessionFor($record, true);
            $this->assertOpen($record, $clinic, $session);
            if (!$session || $session->provider !== 'telemost') $this->invalid('Для записи не подготовлен сеанс Телемоста.');
            if ($session->status === 'ready') {
                if (hash_equals((string) $session->join_url_hash, TelemedicineProviderTransport::telemostUrlHash($url))) return;
                $this->invalid('У этого сеанса уже сохранена встреча. Её адрес нельзя заменить настройками клиники.');
            }
            if (!$this->canRecover($session)) $this->invalid('Создание встречи ещё выполняется. Обновите страницу немного позже.');
            $this->assertUniqueUrl($url, (int) $session->id);
            DB::table(self::TABLE)->where('id', $session->id)->update([
                'status' => 'ready', 'mode' => 'manual_recovery',
                'join_url_ciphertext' => Crypt::encryptString($url),
                'join_url_hash' => TelemedicineProviderTransport::telemostUrlHash($url), 'last_error_code' => null,
                'prepared_at' => now(), 'updated_at' => now(),
            ]);
            $this->event($record, 'provider_prepared_manually', $user, 'telemost', $session->public_id);
        }, 3);
    }

    private function canRecover(object $session): bool
    {
        if ($session->provider !== 'telemost') return false;
        if ($session->status === 'failed') return true;
        if ($session->status !== 'preparing') return false;
        return (int) $session->preparation_started_epoch > 0
            && (int) $session->preparation_started_epoch < time() - 90;
    }

    private function assertUniqueUrl(#[\SensitiveParameter] string $url, ?int $except = null): void
    {
        $query = DB::table(self::TABLE)->where('join_url_hash', TelemedicineProviderTransport::telemostUrlHash($url));
        if ($except !== null) $query->where('id', '!=', $except);
        if ($query->exists()) $this->invalid('Эта встреча уже использована. Для каждого приёма нужна отдельная встреча.', 'manual_join_url');
    }

    private function isAssignedDoctor($user, RegistryRecord $record): bool
    {
        return $user !== null && $user->hasRole('doctors') && Doctor::query()->whereKey($record->doctor_id)
            ->where('clinic_id', $record->clinic_id)->where('user_id', $user->getKey())->exists();
    }

    public function staffContext($user, Patient $patient, int $recordId): array
    {
        $record = $this->participationRecord($user, $patient, $recordId);
        $canManage = $this->journal->canManage($user, $patient);
        $clinic = $this->clinic($record);
        $session = $this->sessionFor($record);
        $reason = $this->closedReason($record, $clinic, $session);
        $window = $this->window($record);
        $profile = ClinicTelemedicineSettings::profile($clinic);
        $ready = $session?->status === 'ready';
        $mayJoin = $ready && $reason === null && $window && time() >= $window['opens'];
        $patientUrl = null;
        if ($ready && $reason === null && $window) {
            $patientUrl = URL::temporarySignedRoute('telemedicine.invitation.show',
                Carbon::createFromTimestampUTC($window['closes']), [
                    'meeting' => $session->public_id, 'start' => $window['start'],
                ]);
        }
        $routeArgs = ['patient' => $patient->getKey(), 'recordId' => $record->getKey()];
        return [
            'record_id' => (int) $record->getKey(), 'clinic_name' => (string) $clinic->name,
            'appointment_time' => ClinicAppointmentTime::describe($record),
            'window' => $window, 'blocked_reason' => $reason,
            'provider' => $session?->provider ?? $profile['provider'],
            'mode' => $session?->mode ?? $profile['telemost_mode'],
            'status' => $session?->status ?? 'not_prepared',
            // Defaults are relevant only before preparation; snapshots keep older sessions intact.
            'setup_errors' => $session ? [] : $profile['settings_errors'],
            'can_prepare' => $canManage && !$session && $reason === null && $profile['settings_errors'] === [],
            'can_recover' => $canManage && $session && $reason === null && $this->canRecover($session),
            'can_manage' => $canManage,
            'can_doctor_join' => $mayJoin && $this->isAssignedDoctor($user, $record),
            'assigned_doctor_user' => $this->isAssignedDoctor($user, $record),
            'patient_invitation_url' => $patientUrl,
            'prepare_url' => route('doctors.telemedicine-session.prepare', $routeArgs),
            'recover_url' => route('doctors.telemedicine-session.recover', $routeArgs),
            'doctor_join_url' => route('doctors.telemedicine-session.doctor-join', $routeArgs),
            'card_url' => url('/doctors/patients/' . $patient->getKey()) . '?tab=telemedicine-log&telemed_open=' . $record->getKey(),
        ];
    }

    public function doctorJoin($user, Patient $patient, int $recordId): string
    {
        return DB::transaction(function () use ($user, $patient, $recordId): string {
            $record = $this->participationRecord($user, $patient, $recordId, true);
            abort_unless($this->isAssignedDoctor($user, $record) && $this->clinicalAccess->canRead($user, $patient), 403,
                'Вход врача доступен назначенному на этот приём специалисту с правом чтения карты.');
            $clinic = $this->clinic($record);
            $session = $this->sessionFor($record, true);
            $this->assertOpen($record, $clinic, $session);
            $window = $this->assertWithinWindow($record);
            $url = $this->joinUrl($record, $clinic, $session, 'doctor', $window);
            $this->event($record, 'provider_opened', $user, $session->provider, $session->public_id,
                $session->room_id, 'doctor');
            return $url;
        }, 3);
    }

    /** Called only after Laravel's signed middleware has verified the invitation URL. */
    public function invitationContext(string $publicId, int $expectedStart): array
    {
        [$record, $clinic, $session, $window] = $this->invitationRecords($publicId, $expectedStart);
        return [
            'clinic_name' => (string) $clinic->name,
            'appointment_time' => ClinicAppointmentTime::describe($record),
            'provider' => $session->provider, 'window' => $window,
            'can_join' => time() >= $window['opens'],
            'join_url' => URL::temporarySignedRoute('telemedicine.invitation.join',
                Carbon::createFromTimestampUTC($window['closes']), [
                    'meeting' => $session->public_id, 'start' => $window['start'],
                ]),
        ];
    }

    private function invitationRecords(string $publicId, int $expectedStart, bool $lock = false): array
    {
        abort_unless($this->ready(), 503, 'Подключение временно недоступно.');
        $session = DB::table(self::TABLE)->where('public_id', $publicId)->first();
        abort_unless($session && $session->status === 'ready', 404);
        $query = RegistryRecord::query()->withTrashed()->whereKey($session->registry_record_id);
        if ($lock) $query->lockForUpdate();
        $record = $query->firstOrFail();
        if ($lock) $session = $this->sessionFor($record, true);
        $clinic = $this->clinic($record);
        $this->assertOpen($record, $clinic, $session);
        $window = $this->window($record);
        abort_unless($window && $window['start'] === $expectedStart, 410,
            'Время приёма изменилось. Получите новое приглашение в клинике.');
        return [$record, $clinic, $session, $window];
    }

    public function patientJoin(string $publicId, int $expectedStart): string
    {
        return DB::transaction(function () use ($publicId, $expectedStart): string {
            [$record, $clinic, $session] = $this->invitationRecords($publicId, $expectedStart, true);
            $window = $this->assertWithinWindow($record);
            $url = $this->joinUrl($record, $clinic, $session, 'patient', $window);
            // Opening a provider is not evidence that the patient joined or was identified.
            $this->audit->appendEvent((int) $record->getKey(), (int) $record->patient_id,
                'provider_opened', 'patient_link', 'patient', null, $session->room_id,
                ['provider' => $session->provider, 'session_id' => $session->public_id]);
            return $url;
        }, 3);
    }

    private function joinUrl(RegistryRecord $record, Clinic $clinic, ?object $session, string $role, array $window): string
    {
        if (!$session || $session->status !== 'ready') $this->invalid('Сначала подготовьте подключение к этому приёму.');
        if ($session->provider === 'webrtc') {
            try {
                $base = TelemedicineProviderTransport::webrtcPage((string) $session->webrtc_url);
                $token = TelemedicineProviderTransport::webrtcToken(
                    ClinicTelemedicineSettings::signalingSecret($clinic), (string) $session->room_id,
                    (int) $record->getKey(), $role, min(time() + 14400, $window['closes']), $window['start']
                );
                return $base . '#token=' . $token;
            } catch (\InvalidArgumentException) {
                $this->invalid('Сервер видеосвязи пока не готов к подключению. Обратитесь в клинику.');
            }
        }
        if ($session->provider === 'telemost') {
            try {
                return TelemedicineProviderTransport::telemostJoinUrl(Crypt::decryptString((string) $session->join_url_ciphertext));
            } catch (\Throwable) {
                $this->invalid('Не удалось прочитать сохранённое приглашение. Обратитесь в клинику.');
            }
        }
        $this->invalid('Неизвестный способ подключения.');
    }

    /** Extra server-to-server gate for NEW managed rooms; legacy journal behavior stays intact. */
    public function signalingAllowed(RegistryRecord $record, ?string $room, ?int $appointmentStart): bool
    {
        if (!Schema::hasTable(self::TABLE)) return true;
        $session = $this->sessionFor($record);
        if (!$session) return true;
        try {
            $clinic = $this->clinic($record);
            $window = $this->window($record);
            return $session->provider === 'webrtc' && $session->status === 'ready'
                && $this->closedReason($record, $clinic, $session) === null
                && is_string($room) && hash_equals((string) $session->room_id, $room)
                && $window && $appointmentStart === $window['start']
                && time() >= $window['opens'] && time() < $window['closes'];
        } catch (\Throwable) { return false; }
    }

    /** Adds navigation only; no token generation, external calls or database writes. */
    public function decorate(Request $request, Patient $patient, array $context): array
    {
        $log = $context['telemedicineLog'] ?? null;
        if (!$this->ready() || !$log || !$request->user()) return $context;
        $canManage = $this->journal->canManage($request->user(), $patient);
        if (!$canManage && !$this->clinicalAccess->canRead($request->user(), $patient)) return $context;
        $ids = $log->getCollection()->pluck('id')->all();
        $query = $canManage ? $this->journal->journalAppointments($request->user(), $patient)
            : RegistryRecord::query()->where('patient_id', $patient->getKey())
                ->whereIn('doctor_id', Doctor::query()->where('user_id', $request->user()->getKey())->select('id'));
        $records = $query->whereKey($ids)->where('appointment_format', 'telemedicine')
            ->whereNull('deleted_at')->get()
            ->filter(fn (RegistryRecord $record): bool => $canManage || $this->isAssignedDoctor($request->user(), $record))->keyBy('id');
        $clinics = Clinic::query()->whereKey($records->pluck('clinic_id')->filter()->unique()->all())->get()->keyBy('id');
        $sessions = DB::table(self::TABLE)->whereIn('registry_record_id', $ids)->get([
            'registry_record_id', 'provider', 'status',
        ])->keyBy('registry_record_id');
        $log->setCollection($log->getCollection()->map(static function (array $call) use ($records, $clinics, $sessions, $patient): array {
            $record = $records->get($call['id']);
            $clinic = $record ? $clinics->get($record->clinic_id) : null;
            if ($record && $clinic && ClinicTelemedicineSettings::enabled($clinic)) {
                $session = $sessions->get($record->getKey());
                $call['provider_session'] = [
                    'url' => route('doctors.telemedicine-session.show', ['patient' => $patient->getKey(), 'recordId' => $record->getKey()]),
                    'label' => $session?->status === 'ready' ? 'Подключение к приёму' : 'Подготовить подключение',
                    'provider' => $session?->provider,
                ];
            }
            return $call;
        }));
        return $context;
    }

    private function event(RegistryRecord $record, string $type, $user, string $provider, string $sessionId, ?string $room = null, string $role = 'system'): void
    {
        $this->audit->appendEvent((int) $record->getKey(), (int) $record->patient_id,
            $type, 'staff', $role, (int) $user->getKey(), $room,
            ['provider' => $provider, 'session_id' => $sessionId]);
    }

    private function invalid(string $message, string $field = 'telemedicine'): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
