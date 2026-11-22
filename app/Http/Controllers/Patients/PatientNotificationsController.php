<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Services\PatientNotificationClinicScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PatientNotificationsController extends Controller
{
    // GET только читает данные; никакой автоматической отметки при открытии вкладки.
    private const NOTIFICATIONS_PER_PAGE = 15;

    public function index(Request $request): View
    {
        $user = $this->patientUser($request);
        $patient = $user->patient()->firstOrFail();
        // NORHNAIM_NOTIFICATION_CLINICS_V1: authorized patient memberships only.
        $clinicScope = app(PatientNotificationClinicScope::class);
        $clinicOptions = $clinicScope->options($patient, $user);
        $selectedClinicId = $clinicScope->selected($request, $clinicOptions);
        $clinicNames = $clinicOptions->mapWithKeys(static fn ($membership): array => [
            (int) $membership->clinic_id => (string) $membership->clinic->name,
        ])->all();
        $filter = (string) $request->query('filter', 'all');
        if (!in_array($filter, ['all', 'unread'], true)) {
            $filter = 'all';
        }

        // «Все» по принятому правилу = только прочитанные.
        $notifications = $clinicScope->query($user, $clinicOptions, $selectedClinicId)
            ->when($filter === 'unread',
                static fn ($query) => $query->whereNull('read_at'),
                static fn ($query) => $query->whereNotNull('read_at'))
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(self::NOTIFICATIONS_PER_PAGE)
            ->withQueryString();

        // Разрешённые ссылки и подписи готовим в контроллере, а не в Blade.
        // Для старых документов пытаемся однозначно найти ID по ключу события
        // с тем же временем создания. При сомнении оставляем ссылку на список.
        $notificationActions = [];
        $hasEventKeys = \Illuminate\Support\Facades\Schema::hasTable('patient_notification_event_keys');
        foreach ($notifications as $notice) {
            $category = (string) data_get($notice->data, 'category', '');
            $rawTarget = data_get($notice->data, 'url');
            $target = is_string($rawTarget) ? $rawTarget : null;
            $isDirectDocument = $category === 'document' && $target !== null
                && preg_match('~^/patient/documents/[0-9]+/preview$~', $target) === 1;

            if ($category === 'document' && !$isDirectDocument && $hasEventKeys) {
                $keys = \Illuminate\Support\Facades\DB::table('patient_notification_event_keys')
                    ->where('user_id', (int) $user->getKey())
                    ->where('created_at', $notice->created_at)
                    ->where('event_key', 'like', 'document:%')
                    ->limit(2)
                    ->pluck('event_key');
                if ($keys->count() === 1 && preg_match(
                    '/^document:(?:clinic|reviewed):([1-9][0-9]*)$/',
                    (string) $keys->first(), $matches
                )) {
                    $documentId = (int) $matches[1];
                    if ($patient->documents()->whereKey($documentId)->exists()) {
                        $target = route('patients.documents.preview', $documentId, false);
                        $isDirectDocument = true;
                    }
                }
            }

            // Никогда не допускаем внешнюю ссылку или javascript: в уведомлении.
            if ($target === null || !str_starts_with($target, '/')
                || str_starts_with($target, '//') || str_contains($target, chr(92))) {
                $target = null;
                $isDirectDocument = false;
            }

            $notificationActions[(string) $notice->id] = [
                'url' => $target,
                'direct' => $isDirectDocument,
                'label' => $isDirectDocument ? 'Просмотреть документ'
                    : ($category === 'document' ? 'Открыть документы' : 'Открыть раздел'),
            ];
        }

        // Метаданные подбираются только среди документов текущего пациента.
        // Старые уведомления без однозначной ссылки на документ не угадываем.
        $documentIdsByNotice = [];
        foreach ($notificationActions as $noticeId => $action) {
            $url = $action['url'] ?? null;
            if (($action['direct'] ?? false) && is_string($url)
                && preg_match('~^/patient/documents/([1-9][0-9]*)/preview$~', $url, $matches) === 1) {
                $documentIdsByNotice[(string) $noticeId] = (int) $matches[1];
            }
        }

        $documentFileMeta = [];
        if ($documentIdsByNotice !== []) {
            // Один запрос к БД на все документы текущей страницы, максимум 15 уведомлений.
            $documentsById = $patient->documents()
                ->whereIn('id', array_values(array_unique($documentIdsByNotice)))
                ->get(['id', 'original_name', 'storage_path', 'mime_type', 'file_size'])
                ->keyBy('id');

            foreach ($documentIdsByNotice as $noticeId => $documentId) {
                $document = $documentsById->get($documentId);
                if ($document === null) {
                    continue;
                }

                $filename = (string) ($document->original_name ?: $document->storage_path ?: '');
                $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
                if (!preg_match('/^[a-z0-9]{1,10}$/', $extension)) {
                    $extension = '';
                }
                if ($extension === '') {
                    $extension = match (strtolower((string) $document->mime_type)) {
                        'application/pdf' => 'pdf',
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'application/msword' => 'doc',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                        'text/plain' => 'txt',
                        default => '',
                    };
                }

                $sizeBytes = $document->file_size !== null
                    ? (int) $document->file_size
                    : null;
                // В старых документах клиники размер в БД мог не сохраняться.
                if ($sizeBytes === null && $document->storage_path) {
                    try {
                        $disk = \Illuminate\Support\Facades\Storage::disk('local');
                        if ($disk->exists((string) $document->storage_path)) {
                            $sizeBytes = $disk->size((string) $document->storage_path);
                        }
                    } catch (\Throwable) {
                        // Отсутствующий файл не должен ломать страницу уведомлений.
                    }
                }

                $parts = [];
                if ($extension !== '') {
                    $parts[] = strtoupper($extension);
                }
                if ($sizeBytes !== null && $sizeBytes >= 0) {
                    if ($sizeBytes >= 1048576) {
                        $parts[] = number_format($sizeBytes / 1048576, 1, ',', ' ') . ' МБ';
                    } elseif ($sizeBytes >= 1024) {
                        $parts[] = number_format($sizeBytes / 1024, 0, ',', ' ') . ' КБ';
                    } else {
                        $parts[] = $sizeBytes . ' Б';
                    }
                }
                if ($parts !== []) {
                    $documentFileMeta[(string) $noticeId] = implode(' · ', $parts);
                }
            }
        }

        return view('patients.notifications.index', [
            'patient' => $patient,
            'notifications' => $notifications,
            'filter' => $filter,
            'notificationActions' => $notificationActions,
            'documentFileMeta' => $documentFileMeta,
            'allCount' => $clinicScope->query($user, $clinicOptions, $selectedClinicId)
                ->whereNotNull('read_at')->count(),
            'unreadCount' => $clinicScope->query($user, $clinicOptions, $selectedClinicId)
                ->whereNull('read_at')->count(),
            'clinicOptions' => $clinicOptions,
            'selectedClinicId' => $selectedClinicId,
            'clinicNames' => $clinicNames,
        ]);
    }

    public function realtimeConfig(Request $request): JsonResponse
    {
        $user = $this->patientUser($request);
        $patient = $user->patient()->firstOrFail();
        $clinicScope = app(PatientNotificationClinicScope::class);
        $clinicOptions = $clinicScope->options($patient, $user);
        $selectedClinicId = $clinicScope->selected($request, $clinicOptions);
        $key = (string) config('broadcasting.connections.reverb.key', '');
        $host = (string) (config('patient_notifications_realtime.host') ?: $request->getHost());
        $port = (int) config('patient_notifications_realtime.port', 8082);
        $secureSetting = config('patient_notifications_realtime.secure');
        $secure = $secureSetting === null
            ? $request->isSecure()
            : filter_var($secureSetting, FILTER_VALIDATE_BOOLEAN);

        // Application key is intentionally public; REVERB_APP_SECRET is NEVER sent to clients.
        $enabled = $key !== '' && $host !== '' && $port > 0 && $port <= 65535;

        return response()->json([
            'enabled' => $enabled,
            'key' => $enabled ? $key : '',
            'host' => $host,
            'port' => $port,
            'secure' => $secure,
            'channel' => 'private-patient.notifications.' . $user->getKey(),
            'event' => 'patient.notifications.counts.updated',
            'authUrl' => route('patients.notifications.ws-auth', [], false),
            'csrf' => csrf_token(),
            // Global counters always include every clinic (and legacy unassigned notices).
            'counts' => [
                'unread' => (int) $user->unreadNotifications()->count(),
                'read' => (int) $user->notifications()->whereNotNull('read_at')->count(),
            ],
            // Visible tab counters relate only to the selected clinic.
            'selectedCounts' => [
                'unread' => (int) $clinicScope->query($user, $clinicOptions, $selectedClinicId)
                    ->whereNull('read_at')->count(),
                'read' => (int) $clinicScope->query($user, $clinicOptions, $selectedClinicId)
                    ->whereNotNull('read_at')->count(),
            ],
        ])->header('Cache-Control', 'private, no-store, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['count' => $this->patientUser($request)->unreadNotifications()->count()]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        // Помечаем ровно одно уведомление и только у текущего пациента.
        $user = $this->patientUser($request);
        $patient = $user->patient()->firstOrFail();
        $clinicScope = app(PatientNotificationClinicScope::class);
        $selectedClinicId = $clinicScope->selected($request, $clinicScope->options($patient, $user));
        $item = $user->notifications()->whereKey($notification)->firstOrFail();
        if ($item->read_at === null) {
            $item->markAsRead();
            app(\App\Services\PatientNotificationRealtimePublisher::class)
                ->publishAfterCommit((int) $user->getKey());
        }

        // После отметки возвращаемся к первой странице непрочитанных:
        // смещение изменилось, поэтому нельзя слепо оставаться на странице N.
        return redirect()->route('patients.notifications.index', [
            'filter' => 'unread',
            'clinic_id' => $selectedClinicId,
        ])
            ->with('status', 'Уведомление отмечено прочитанным.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $this->patientUser($request);
        $patient = $user->patient()->firstOrFail();
        $clinicScope = app(PatientNotificationClinicScope::class);
        $clinicOptions = $clinicScope->options($patient, $user);
        $selectedClinicId = $clinicScope->selected($request, $clinicOptions);
        $clinicScope->query($user, $clinicOptions, $selectedClinicId)
            ->whereNull('read_at')->update(['read_at' => now(), 'updated_at' => now()]);
        app(\App\Services\PatientNotificationRealtimePublisher::class)
            ->publishAfterCommit((int) $user->getKey());

        return redirect()->route('patients.notifications.index', ['clinic_id' => $selectedClinicId])
            ->with('status', 'Все уведомления отмечены как прочитанные.');
    }

    private function patientUser(Request $request): object
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients') && $user->patient()->exists(), 403);

        return $user;
    }
}
