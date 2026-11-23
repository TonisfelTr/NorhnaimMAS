<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Services\ClinicTelemedicineSettings;
use App\Services\TelemedicineProviderSessionService;
use App\Support\ClinicAppointmentTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** NORHNAIM patient-facing telemedicine page. */
final class PatientTelemedicineController extends Controller
{
    private const PER_PAGE = 10;

    private function currentPatient(Request $request): Patient
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('patients'), 403);

        return $user->patient()->firstOrFail();
    }

    public function index(Request $request)
    {
        $patient = $this->currentPatient($request);

        abort_unless(
            Schema::hasTable('telemedicine_provider_sessions'),
            503,
            'Раздел телемедицины временно недоступен. Обратитесь в клинику.'
        );

        // Every row is scoped to the authenticated patient's own patient_id.
        // withTrashed() is intentional: cancelled appointments belong in history.
        $query = RegistryRecord::withTrashed()
            ->with(['doctor.media'])
            ->where('patient_id', $patient->getKey())
            ->where('appointment_format', 'telemedicine');

        ClinicAppointmentTime::orderByStart($query, 'desc');

        $records = $query->orderByDesc('id')->get();
        $recordIds = $records->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $clinicIds = $records->pluck('clinic_id')->filter()->unique()->values()->all();
        $clinics = $clinicIds !== []
            ? Clinic::query()->whereIn('id', $clinicIds)->get()->keyBy('id')
            : collect();

        $sessions = $recordIds !== []
            ? DB::table('telemedicine_provider_sessions')
                ->whereIn('registry_record_id', $recordIds)
                ->where('patient_id', $patient->getKey())
                ->get([
                    'registry_record_id',
                    'public_id',
                    'patient_id',
                    'clinic_id',
                    'doctor_id',
                    'provider',
                    'status',
                    'prepared_at',
                ])
                ->keyBy('registry_record_id')
            : collect();

        $confirmations = $recordIds !== [] && Schema::hasTable('medical_visit_confirmations')
            ? DB::table('medical_visit_confirmations')
                ->whereIn('registry_record_id', $recordIds)
                ->get(['registry_record_id', 'outcome'])
                ->groupBy('registry_record_id')
            : collect();

        $encounters = collect();
        if ($recordIds !== [] && Schema::hasTable('medical_encounters')) {
            $available = Schema::getColumnListing('medical_encounters');
            $columns = array_values(array_intersect(
                [
                    'id',
                    'registry_record_id',
                    'consultation_status',
                    'conclusion',
                    'recommendations',
                    'completed_at',
                    'updated_at',
                ],
                $available
            ));

            if (in_array('registry_record_id', $columns, true)) {
                $encounterQuery = DB::table('medical_encounters')
                    ->whereIn('registry_record_id', $recordIds);

                if (in_array('id', $columns, true)) {
                    $encounterQuery->orderByDesc('id');
                }

                $encounters = $encounterQuery->get($columns)->groupBy('registry_record_id');
            }
        }

        $now = time();
        $joinBeforeMinutes = max(0, min(
            240,
            (int) config('clinic_telemedicine.join_before_minutes', 15)
        ));
        $joinAfterMinutes = max(1, min(
            1440,
            (int) config('clinic_telemedicine.join_after_minutes', 120)
        ));

        $rows = $records->map(function (RegistryRecord $record) use (
            $sessions,
            $clinics,
            $confirmations,
            $encounters,
            $now,
            $joinBeforeMinutes,
            $joinAfterMinutes
        ): array {
            $recordId = (int) $record->getKey();
            $start = ClinicAppointmentTime::startInstant($record);

            try {
                $time = ClinicAppointmentTime::describe($record);
                $localStart = ClinicAppointmentTime::displayDate($record);
            } catch (\Throwable) {
                $time = [
                    'date' => null,
                    'time' => null,
                    'timezone_label' => 'часовой пояс не определён',
                    'legacy' => true,
                ];
                $localStart = ClinicAppointmentTime::legacyLocal($record);
            }

            $confirmationRows = $confirmations->get($recordId, collect());
            $encounterRows = $encounters->get($recordId, collect());
            $encounter = $encounterRows->first(
                static fn ($row): bool => ($row->consultation_status ?? null) === 'completed'
            ) ?? $encounterRows->first();

            // A completed / not-held result is shown only when it was explicitly
            // recorded by the clinician or in the clinical encounter.
            $held = $confirmationRows->contains(
                static fn ($row): bool => ($row->outcome ?? null) === 'held'
            ) || $encounterRows->contains(
                static fn ($row): bool => ($row->consultation_status ?? null) === 'completed'
            );

            $notHeld = $confirmationRows->contains(
                static fn ($row): bool => ($row->outcome ?? null) === 'not_held'
            ) || $encounterRows->contains(
                static fn ($row): bool => ($row->consultation_status ?? null) === 'not_held'
            );

            $session = $sessions->get($recordId);
            if ($session && (
                (int) $session->registry_record_id !== $recordId
                || (int) $session->patient_id !== (int) $record->patient_id
                || (int) $session->clinic_id !== (int) $record->clinic_id
                || (int) $session->doctor_id !== (int) $record->doctor_id
            )) {
                $session = null;
            }

            $clinic = $clinics->get((int) $record->clinic_id);
            $doctor = $record->doctor;
            $doctorIsValid = $doctor !== null
                && (int) $doctor->clinic_id === (int) $record->clinic_id;
            $clinicTelemedicineEnabled = $clinic !== null
                && ClinicTelemedicineSettings::enabled($clinic);

            $startEpoch = $start?->getTimestamp();
            $opensAt = $startEpoch !== null
                ? $startEpoch - ($joinBeforeMinutes * 60)
                : null;
            $closesAt = $startEpoch !== null
                ? $startEpoch + ($joinAfterMinutes * 60)
                : null;

            $cancelled = $record->deleted_at !== null;
            $windowExpired = $closesAt !== null && $now >= ($closesAt - 60);

            $canJoin = !$cancelled
                && !$held
                && !$notHeld
                && $startEpoch !== null
                && $opensAt !== null
                && $closesAt !== null
                && $now >= $opensAt
                && $now < ($closesAt - 60)
                && $session !== null
                && $session->status === 'ready'
                && $clinicTelemedicineEnabled
                && $doctorIsValid;

            $status = 'Ожидает подготовки клиникой';
            $statusType = 'warning';
            $statusHint = 'Клиника подготовит видеосеанс к назначенному времени.';

            if ($cancelled) {
                $status = 'Запись отменена';
                $statusType = 'muted';
                $statusHint = 'Эта консультация была отменена клиникой.';
            } elseif ($held) {
                $status = 'Консультация проведена';
                $statusType = 'success';
                $statusHint = 'Проведение консультации подтверждено врачом.';
            } elseif ($notHeld) {
                $status = 'Консультация не состоялась';
                $statusType = 'danger';
                $statusHint = 'Врач отметил, что консультация не состоялась.';
            } elseif ($windowExpired) {
                // Expiration of the technical join window is NOT a medical
                // confirmation that the patient missed the appointment.
                $status = 'Ожидает подтверждения врача';
                $statusType = 'warning';
                $statusHint = 'Окно подключения завершено. Результат консультации пока не подтверждён.';
            } elseif ($startEpoch === null) {
                $status = 'Время приёма требует уточнения';
                $statusType = 'warning';
                $statusHint = 'Обратитесь в клинику, чтобы уточнить время консультации.';
            } elseif (!$clinic) {
                $status = 'Клиника временно недоступна';
                $statusType = 'warning';
                $statusHint = 'Клиника проверит настройки видеосвязи.';
            } elseif (!$clinicTelemedicineEnabled) {
                $status = 'Телемедицина временно отключена';
                $statusType = 'warning';
                $statusHint = 'Клиника уточнит возможность проведения дистанционной консультации.';
            } elseif (!$doctorIsValid) {
                $status = 'Назначенный врач недоступен';
                $statusType = 'warning';
                $statusHint = 'Свяжитесь с клиникой для уточнения записи.';
            } elseif ($session?->status === 'preparing') {
                $status = 'Подготавливается видеосвязь';
                $statusType = 'warning';
                $statusHint = 'Клиника готовит видеосеанс. Кнопка подключения появится автоматически.';
            } elseif ($session?->status === 'failed') {
                $status = 'Требуется повторная подготовка';
                $statusType = 'warning';
                $statusHint = 'Клиника должна повторно подготовить видеосеанс.';
            } elseif (!$session) {
                $status = 'Ожидает подготовки клиникой';
                $statusType = 'warning';
                $statusHint = 'Ссылка на видеоконсультацию появится после подготовки сеанса клиникой.';
            } elseif ($session->status !== 'ready') {
                $status = 'Подключение пока недоступно';
                $statusType = 'warning';
                $statusHint = 'Видеосеанс ещё не готов к подключению.';
            } elseif ($now < $opensAt) {
                $status = 'Запланирована';
                $statusType = 'info';
                $statusHint = 'Подключение откроется за ' . $joinBeforeMinutes . ' минут до начала консультации.';
            } else {
                $status = 'Можно подключиться';
                $statusType = 'success';
                $statusHint = 'Видеосеанс готов. Можно подключиться к консультации.';
            }

            $isHistory = $cancelled || $held || $notHeld || $windowExpired;
            $doctorName = $doctor
                ? trim(implode(' ', array_filter([
                    $doctor->surname,
                    $doctor->name,
                    $doctor->patronym,
                ])))
                : '';

            $doctorPhotoUrl = null;
            if ($doctor && method_exists($doctor, 'getFirstMedia')) {
                $doctorPhotoUrl = $doctor->getFirstMedia('photos')?->getUrl();
            }

            $doctorSpeciality = $doctor
                ? trim((string) ($doctor->profession ?: $doctor->status ?: 'Лечащий врач'))
                : 'Лечащий врач';

            $conclusion = trim((string) ($encounter?->conclusion ?? ''));
            $recommendations = trim((string) ($encounter?->recommendations ?? ''));
            $encounterCompleted = ($encounter?->consultation_status ?? null) === 'completed';
            $hasResult = $encounterCompleted && ($conclusion !== '' || $recommendations !== '');
            $completedAt = $encounter?->completed_at ?? $encounter?->updated_at ?? null;

            $providerLabel = match ($session?->provider) {
                'telemost' => 'Яндекс Телемост',
                'webrtc' => 'Видеосвязь клиники',
                default => 'Видеосвязь клиники',
            };

            // clck.ru gets only a protected Norhnaim URL; no WebRTC bearer token.
            $shortJoinUrl = null;
            if ($session && $session->status === 'ready'
                && $clinicTelemedicineEnabled && $doctorIsValid
                && !$cancelled && !$isHistory && $startEpoch !== null) {
                $shortJoinUrl = app(\App\Services\TelemedicineShortLinkService::class)
                    ->url((string) $session->public_id, 'patient', (int) $startEpoch);
            }

            return [
                'id' => $recordId,
                'date' => $time['date'] ?? 'Дата не определена',
                'time' => $time['time'] ?? '—',
                'timezone_label' => $time['timezone_label'] ?? 'часовой пояс не определён',
                'duration' => max(1, (int) ($record->appointment_duration_minutes ?: 30)),
                'doctor_name' => $doctorName !== '' ? $doctorName : 'Врач не указан',
                'doctor_speciality' => $doctorSpeciality,
                'doctor_photo_url' => $doctorPhotoUrl,
                'clinic_name' => $clinic?->name ?: 'Клиника не указана',
                'provider_label' => $providerLabel,
                'status' => $status,
                'status_type' => $statusType,
                'status_hint' => $statusHint,
                'can_join' => $canJoin,
                'short_join_url' => $shortJoinUrl,
                'join_opens_at' => $session?->status === 'ready'
                    && $startEpoch !== null
                    && $opensAt !== null
                    && $now < $opensAt
                    && !$isHistory
                        ? $opensAt
                        : null,
                'join_opens_label' => $localStart && $startEpoch !== null
                    ? $localStart->modify('-' . $joinBeforeMinutes . ' minutes')->format('H:i')
                    : null,
                'history' => $isHistory,
                'has_result' => $hasResult,
                'result_conclusion' => $hasResult ? $conclusion : '',
                'result_recommendations' => $hasResult ? $recommendations : '',
                'result_completed_at' => $hasResult && $completedAt
                    ? \Illuminate\Support\Carbon::parse($completedAt)->format('d.m.Y H:i')
                    : null,
                'sort_at' => $startEpoch ?? $localStart?->getTimestamp() ?? 0,
            ];
        })->values();

        $upcoming = $rows
            ->where('history', false)
            ->sortBy('sort_at')
            ->values();

        $history = $rows
            ->where('history', true)
            ->sortByDesc('sort_at')
            ->values();

        $activeTab = (string) $request->query('tab', 'upcoming');
        if (!in_array($activeTab, ['upcoming', 'history'], true)) {
            $activeTab = 'upcoming';
        }

        $historyPage = $this->paginateCollection(
            $history,
            self::PER_PAGE,
            'history_page',
            $request
        );

        return response()
            ->view('patients.telemedicine.index', [
                'patient' => $patient,
                'activeTab' => $activeTab,
                'upcoming' => $upcoming,
                'nearestUpcoming' => $upcoming->first(),
                'otherUpcoming' => $upcoming->slice(1)->values(),
                'recentHistory' => $history->take(2)->values(),
                'historyPage' => $historyPage,
                'upcomingCount' => $upcoming->count(),
                'historyCount' => $history->count(),
                'resultCount' => $rows->where('has_result', true)->count(),
                'joinableCount' => $upcoming->where('can_join', true)->count(),
            ])
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    private function paginateCollection(
        Collection $items,
        int $perPage,
        string $pageName,
        Request $request
    ): LengthAwarePaginator {
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, LengthAwarePaginator::resolveCurrentPage($pageName)), $lastPage);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }

    public function join(
        Request $request,
        int $record,
        TelemedicineProviderSessionService $sessions
    ): RedirectResponse {
        $patient = $this->currentPatient($request);

        abort_unless(
            Schema::hasTable('telemedicine_provider_sessions'),
            503,
            'Подключение к телемедицине временно недоступно.'
        );

        // Scope the appointment to the authenticated patient before using
        // the provider session's opaque public identifier.
        $appointment = RegistryRecord::query()
            ->where('patient_id', $patient->getKey())
            ->where('appointment_format', 'telemedicine')
            ->whereKey($record)
            ->firstOrFail();

        $start = ClinicAppointmentTime::startInstant($appointment);
        abort_unless(
            $start !== null,
            409,
            'Для этой записи не подтверждено время с часовым поясом. Обратитесь в клинику.'
        );

        $session = DB::table('telemedicine_provider_sessions')
            ->where('registry_record_id', $appointment->getKey())
            ->where('patient_id', $patient->getKey())
            ->where('clinic_id', $appointment->clinic_id)
            ->where('doctor_id', $appointment->doctor_id)
            ->where('status', 'ready')
            ->first(['public_id']);

        abort_unless(
            $session !== null,
            409,
            'Клиника ещё не подготовила подключение к этой консультации.'
        );

        // The existing service repeats all authorization, provider snapshot,
        // clinic setting, outcome and allowed-time checks before redirecting.
        $url = $sessions->patientJoin(
            (string) $session->public_id,
            $start->getTimestamp()
        );

        return redirect()
            ->away($url)
            ->withHeaders([
                'Cache-Control' => 'private, no-store',
                'Referrer-Policy' => 'no-referrer',
            ]);
    }
}
