@extends('layouts.doctor')
@section('title', 'Уведомления о видеосеансах')
@section('main')
<link rel="stylesheet" href="{{ asset('css/doctor-telemedicine-reminders.css') }}?v=1">
<div class="tmrn-wrap">
    <div class="tmrn-head">
        <div>
            <div class="tmrn-eyebrow">ПАНЕЛЬ ВРАЧА</div>
            <h1>Уведомления о видеосеансах</h1>
            <p>Напоминания о предстоящих консультациях. SMS на личный номер настраивается в профиле.</p>
        </div>
        <a class="tmrn-link" href="{{ route('doctors.profile.index') }}">Настройки SMS</a>
    </div>
    @forelse($items as $notice)
        @php $data = $notice->data; @endphp
        <article class="tmrn-item {{ $notice->read_at ? '' : 'is-unread' }}">
            <div class="tmrn-icon"><i class="bi bi-camera-video"></i></div>
            <div class="tmrn-body">
                <strong>{{ $data['title'] ?? 'Видеосеанс' }}</strong>
                <p>{{ $data['message'] ?? '' }}</p>
                <small>{{ $notice->created_at?->format('d.m.Y H:i') }}</small>
            </div>
            <div class="tmrn-actions">
                <a class="tmrn-link" href="{{ $data['url'] ?? route('doctors.main') }}">Открыть сеанс</a>
                @if(!$notice->read_at)
                    <form method="post" action="{{ route('doctors.telemedicine-reminders.read', $notice->id) }}">
                        @csrf
                        <button class="tmrn-mute" type="submit">Прочитано</button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="tmrn-empty">Уведомлений о предстоящих видеосеансах пока нет.</div>
    @endforelse
    @if($items->hasPages())<div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
