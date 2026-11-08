<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer"><meta name="robots" content="noindex,nofollow">
    <title>Приглашение на консультацию</title>
    <link rel="stylesheet" href="{{ asset('css/clinic-telemedicine-session.css') }}">
</head>
<body class="ctm-public">
<main class="ctm-session ctm-session--public">
    <p class="ctm-eyebrow">{{ $clinic_name }}</p>
    <h1>Ваша онлайн-консультация</h1>
    <section class="ctm-card">
        <h2>Время приёма</h2>
        <p class="ctm-main-time"
           data-appointment-primary
           data-appointment-utc="{{ $appointment_time['iso_utc'] ?? $appointment_time['iso'] ?? '' }}">
            <span data-appointment-primary-date>{{ $appointment_time['date'] }}</span>
            <span data-appointment-primary-time>{{ $appointment_time['time'] }}</span>
            <span data-appointment-primary-zone>{{ $appointment_time['timezone_label'] }}</span>
        </p>
        @include('shared.appointment_timezone_display', ['appointmentTime' => $appointment_time, 'showViewerTime' => false])
        @include('shared.appointment_timezone_control')
        <p class="ctm-muted">Подключиться можно за {{ $window['before_minutes'] }} мин. до начала. Окно подключения — до {{ $window['after_minutes'] }} мин. после начала приёма.</p>
    </section>
    <section class="ctm-card">
        <h2>{{ $provider === 'telemost' ? 'Яндекс Телемост' : 'Видеосвязь с клиникой' }}</h2>
        <p>Перед подключением проверьте камеру и микрофон. При входе врач уточнит вашу личность.</p>
        @if($provider === 'telemost')<p>После перехода ожидайте допуска организатором встречи.</p>@endif
        <form method="post" action="{{ $join_url }}">@csrf
            <button class="ctm-button" type="submit" @disabled(!$can_join)>Подключиться к консультации</button>
        </form>
        @if(!$can_join)
            <p class="ctm-muted">Вход пока закрыт. Обновите страницу ближе к началу приёма.</p>
            <a class="ctm-back" href="{{ request()->fullUrl() }}">Обновить страницу</a>
        @endif
    </section>
    <p class="ctm-muted">Это персональное приглашение. Не публикуйте ссылку и не передавайте её другим людям.</p>
</main>
</body>
</html>
