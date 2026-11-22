@extends('layouts.patient')

@section('title', 'Уведомления')

@section('content')
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

    <div class="patient-notifications-toolbar">
        <div class="patient-notifications-tabs">
            <a href="{{ route('patients.notifications.index', ['filter' => 'all']) }}"
               @class(['is-active' => $filter === 'all'])>Все</a>
            <a href="{{ route('patients.notifications.index', ['filter' => 'unread']) }}"
               @class(['is-active' => $filter === 'unread'])>
                Непрочитанные
                @if($unreadCount > 0)<span>{{ $unreadCount }}</span>@endif
            </a>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('patients.notifications.read-all') }}">
                @csrf
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
                            default => 'Личное уведомление',
                        } }}</span>
                    </div>
                </div>
                @unless($notification->read_at)
                    <form method="POST" action="{{ route('patients.notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="patient-notification-open" aria-label="Прочитать и открыть уведомление">
                            <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </button>
                    </form>
                @else
                    <span class="patient-notification-read-icon" title="Прочитано">
                        <i class="bi bi-check2" aria-hidden="true"></i>
                    </span>
                @endunless
            </article>
        @empty
            <div class="patient-activity-empty">
                <span><i class="bi bi-bell-slash" aria-hidden="true"></i></span>
                <h3>{{ $filter === 'unread' ? 'Непрочитанных уведомлений нет' : 'Уведомлений пока нет' }}</h3>
                <p>Здесь появятся сообщения о записях, консультациях и других важных событиях.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="patient-activity-pagination">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
