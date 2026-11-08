@extends('layouts.doctor')

@section('title', 'Подключение к приёму')
@section('assets')
    @parent
    <link rel="stylesheet" href="{{ asset('css/clinic-telemedicine-session.css') }}">
@endsection

@section('main')
<main class="ctm-session">
    <a class="ctm-back" href="{{ $card_url }}">← Журнал телемедицины</a>
    <header class="ctm-session__header">
        <div>
            <p class="ctm-eyebrow">Приём № {{ $record_id }}</p>
            <h1>Подключение к приёму</h1>
            <p>{{ $clinic_name }}</p>
        </div>
        <span class="ctm-badge">{{ $provider === 'telemost' ? 'Яндекс Телемост' : 'Собственная видеосвязь' }}</span>
    </header>

    @if($errors->any())
        <div class="ctm-notice ctm-notice--error" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif

    <section class="ctm-card">
        <h2>Время консультации</h2>
        <p class="ctm-main-time">{{ $appointment_time['date'] ?? 'Дата не задана' }} {{ $appointment_time['time'] ?? '' }}
            <span>{{ $appointment_time['timezone_label'] }}</span></p>
        @include('shared.appointment_timezone_display', ['appointmentTime' => $appointment_time])
        @include('shared.appointment_timezone_control')
        @if($window)
            <p class="ctm-muted">Вход открывается за {{ $window['before_minutes'] }} мин. до начала. Окно подключения — до {{ $window['after_minutes'] }} мин. после начала приёма.</p>
        @endif
    </section>

    @if($blocked_reason)
        <div class="ctm-notice" role="status">{{ $blocked_reason }}</div>
    @endif
    @if($setup_errors)
        <div class="ctm-notice" role="status">
            <strong>Нужно завершить настройки клиники</strong>
            <ul>@foreach($setup_errors as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    @if($status === 'not_prepared' && !$can_manage && !$blocked_reason)
        <div class="ctm-notice">Подключение ещё не подготовлено. Обратитесь к сотруднику, который управляет расписанием этого пациента.</div>
    @endif

    @if($can_prepare)
        <section class="ctm-card">
            <h2>Подготовить связь</h2>
            <p>Подключение создаётся для этого приёма после нажатия кнопки.</p>
            <form method="post" action="{{ $prepare_url }}">
                @csrf
                @if($provider === 'telemost' && $mode === 'manual')
                    <label class="ctm-label" for="ctm-manual-url">Ссылка отдельной встречи в Телемосте</label>
                    <input class="ctm-input" type="url" id="ctm-manual-url" name="manual_join_url" maxlength="2048" required
                           placeholder="https://telemost.yandex.ru/j/…" autocomplete="off">
                    <p class="ctm-muted">Создайте новую встречу для этого пациента. В Телемосте включите зал ожидания и предоставьте врачу права организатора или соорганизатора.</p>
                @elseif($provider === 'telemost')
                    <p class="ctm-muted">МИС создаст отдельную встречу с залом ожидания. Данные пациента и диагноз в запрос не передаются.</p>
                @endif
                <button class="ctm-button" type="submit">{{ $provider === 'telemost' && $mode === 'manual' ? 'Сохранить встречу' : 'Подготовить подключение' }}</button>
            </form>
        </section>
    @endif

    @if(in_array($status, ['preparing', 'failed'], true))
        <section class="ctm-card">
            <h2>{{ $status === 'preparing' ? 'Создание встречи выполняется' : 'Результат создания не подтверждён' }}</h2>
            <p>Обновите страницу. Если результат не появился, проверьте встречи в аккаунте организатора Телемоста. Повторного автоматического запроса МИС не отправляет.</p>
            @if($can_recover)
                <form method="post" action="{{ $recover_url }}">
                    @csrf
                    <label class="ctm-label" for="ctm-recover-url">Ссылка существующей отдельной встречи</label>
                    <input class="ctm-input" type="url" id="ctm-recover-url" name="manual_join_url" maxlength="2048" required autocomplete="off" placeholder="https://telemost.yandex.ru/j/…">
                    <label class="ctm-check"><input type="checkbox" name="manual_existing_confirmed" value="1" required> Я проверил встречи организатора и выбрал отдельную встречу для этого приёма.</label>
                    <button class="ctm-button" type="submit">Прикрепить проверенную встречу</button>
                </form>
            @endif
        </section>
    @endif

    @if($status === 'ready' && !$blocked_reason)
        <div class="ctm-grid">
            <section class="ctm-card">
                <h2>Вход врача</h2>
                @if($assigned_doctor_user)
                    <form method="post" action="{{ $doctor_join_url }}">@csrf
                        <button class="ctm-button" type="submit" @disabled(!$can_doctor_join)>Войти в консультацию</button>
                    </form>
                    @if(!$can_doctor_join)<p class="ctm-muted">Кнопка станет доступна в окне подключения. Обновите страницу ближе к началу приёма.</p>@endif
                @else
                    <p>Вход под ролью врача доступен пользователю специалиста, назначенного на этот приём.</p>
                @endif
                @if($provider === 'telemost')
                    <p class="ctm-muted">Для допуска пациента из зала ожидания врач должен войти в Телемост как организатор или соорганизатор встречи.</p>
                @endif
            </section>
            <section class="ctm-card">
                <h2>Приглашение пациента</h2>
                @if($patient_invitation_url)
                    <label class="ctm-label" for="ctm-patient-link">Скопируйте ссылку и передайте пациенту</label>
                    <input class="ctm-input ctm-link" id="ctm-patient-link" type="text" readonly value="{{ $patient_invitation_url }}" autocomplete="off" spellcheck="false">
                    <p class="ctm-muted">Ссылка действует для одного приёма. После переноса времени выдайте новое приглашение. Автоматическая отправка не выполняется.</p>
                @endif
            </section>
        </div>
        <p class="ctm-muted">Открытие видеосвязи не подтверждает личность пациента и факт проведения консультации. Результат приёма врач отмечает в журнале.</p>
        @if($provider === 'telemost')
            <p class="ctm-muted">Отмена записи закрывает приглашение МИС. Встречу, уже открытую непосредственно в Телемосте, организатор завершает в самом Телемосте.</p>
        @endif
    @endif
</main>
@endsection
