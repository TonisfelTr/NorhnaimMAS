@extends('layouts.patient')

@section('title', 'Уведомления')

@section('content')
<link rel="stylesheet" href="{{ asset('css/norhnaim-notification-clinic-search-v1.css') }}?v=1">
<script defer src="{{ asset('js/norhnaim-notification-clinic-search-v1.js') }}?v=1"></script>
<link rel="stylesheet" href="{{ asset('css/norhnaim-patient-notification-actions-v5.css') }}?v=5">
<link rel="stylesheet" href="{{ asset('css/norhnaim-notification-clinics-v1.css') }}?v=1">
<div class="patient-activity-page">
    <div class="patient-activity-heading">
        <div>
            <div class="patient-activity-eyebrow">ЛИЧНЫЙ КАБИНЕТ</div>
            <h1>Уведомления</h1>
            <p>Важные изменения, связанные с вашими записями и лечением.</p>
        </div>
        <div class="patient-activity-heading__icon">
            <i class="bi bi-bell" aria-hidden="true"></i>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    {{-- NORHNAIM_NOTIFICATION_CLINICS_V1 --}}
    @if($clinicOptions->isNotEmpty())
        <section class="patient-notifications-clinic-switcher" aria-label="Фильтр уведомлений по клинике">
            <div class="patient-notifications-clinic-copy">
                <span class="patient-notifications-clinic-label">Клиника</span>
                <span class="patient-notifications-clinic-description">Показываются уведомления выбранной клиники</span>
            </div>
            <form method="GET" action="{{ route('patients.notifications.index') }}">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <label for="notificationClinicSelect" class="visually-hidden">Выберите клинику</label>
                <select name="clinic_id" id="notificationClinicSelect" onchange="this.form.requestSubmit()">
                    <option value="" @selected($selectedClinicId === null)>Все клиники</option>
                    @foreach($clinicOptions as $membership)
                        <option value="{{ $membership->clinic_id }}" @selected((int) $selectedClinicId === (int) $membership->clinic_id)>
                            {{ $membership->clinic->name }}{{ $membership->is_primary ? ' · основная' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </section>
    @endif

    <div class="patient-notifications-toolbar">
        <div class="patient-notifications-tabs">
            <a href="{{ route('patients.notifications.index', ['filter' => 'all', 'clinic_id' => $selectedClinicId]) }}"
               @class(['is-active' => $filter === 'all'])
               aria-label="Прочитанные уведомления: {{ $allCount }}" title="Только прочитанные">
                Все <span data-notification-count="read">{{ $allCount }}</span>
            </a>
            <a href="{{ route('patients.notifications.index', ['filter' => 'unread', 'clinic_id' => $selectedClinicId]) }}"
               @class(['is-active' => $filter === 'unread'])
               aria-label="Непрочитанные уведомления: {{ $unreadCount }}">
                Непрочитанные <span data-notification-count="unread">{{ $unreadCount }}</span>
            </a>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('patients.notifications.read-all') }}" data-notification-read-all-form>
                @csrf
                <input type="hidden" name="clinic_id" value="{{ $selectedClinicId }}">
                <button type="submit" class="patient-notifications-read-all">Отметить все прочитанными</button>
            </form>
        @endif
    </div>

    <div class="patient-notifications-list">
        @forelse($notifications as $notification)
            <article class="patient-notification-card {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                <span class="patient-notification-card__icon">
                    <i class="bi bi-{{ data_get($notification->data, 'icon', 'bell') }}" aria-hidden="true"></i>
                </span>
                <div class="patient-notification-card__body">
                    <div class="patient-notification-card__top">
                        <h3>{{ data_get($notification->data, 'title', 'Уведомление') }}</h3>
                        @unless($notification->read_at)
                            <span class="patient-notification-new">Новое</span>
                        @endunless
                    </div>
                    <p>{{ data_get($notification->data, 'message', '') }}</p>
                    <div class="patient-notification-card__footer">
                        
                        <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                            {{ $notification->created_at?->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                        </time>
                        <span>{{ match((string) data_get($notification->data, 'category', '')) {
                            'appointment' => 'Записи',
                            'consultation' => 'Приёмы',
                            'document' => 'Документы',
                            'research' => 'Исследования',
                            'assignment' => 'Назначения',
                            'telemedicine' => 'Телемедицина',
                            'test' => 'Тестирования',
                            default => 'Личное уведомление',
                        } }}</span>
                        @if($selectedClinicId === null)
                            <span class="patient-notification-card__clinic">· {{ $notification->clinic_id
                                ? ($clinicNames[(int) $notification->clinic_id] ?? 'Клиника')
                                : 'Клиника не определена' }}</span>
                        @endif
                        @if(!empty($documentFileMeta[(string) $notification->id]))
                            <span class="patient-notification-card__file-meta">· {{ $documentFileMeta[(string) $notification->id] }}</span>
                        @endif
                    </div>
                </div>
                <div class="patient-notification-card__actions">
                    @if(($notificationActions[(string) $notification->id]['url'] ?? null) !== null)
                        <a class="patient-notification-action"
                           href="{{ $notificationActions[(string) $notification->id]['url'] }}"
                           @if($notificationActions[(string) $notification->id]['direct']) target="_blank" rel="noopener noreferrer" @endif
                           aria-label="{{ $notificationActions[(string) $notification->id]['label'] }}">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                            <span>{{ $notificationActions[(string) $notification->id]['label'] }}</span>
                        </a>
                    @endif
                    @unless($notification->read_at)
                        <form method="POST" action="{{ route('patients.notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="clinic_id" value="{{ $selectedClinicId }}">
                            <button type="submit" class="patient-notification-action patient-notification-action--mark"
                                    aria-label="Отметить уведомление прочитанным">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                <span>Отметить прочитанным</span>
                            </button>
                        </form>
                    @else
                        <span class="patient-notification-manual-read-state" title="Уведомление прочитано">
                            <i class="bi bi-check2" aria-hidden="true"></i> Прочитано
                        </span>
                    @endunless
                </div>
            </article>
        @empty
            <div class="patient-activity-empty">
                <span><i class="bi bi-bell-slash" aria-hidden="true"></i></span>
                <h3>{{ $filter === 'unread' ? 'Непрочитанных уведомлений нет' : 'Прочитанных уведомлений пока нет' }}</h3>
                <p>Здесь появятся сообщения о записях, консультациях и других важных событиях.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="patient-activity-pagination">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
