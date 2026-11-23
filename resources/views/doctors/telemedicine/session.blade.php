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
                
                @if(!empty($doctor_short_url))
                    <label class="ctm-label" for="ctm-doctor-short-url">Короткая ссылка врача (clck.ru)</label>
                    <input class="ctm-input ctm-link" id="ctm-doctor-short-url" type="text" readonly
                           value="{{ $doctor_short_url }}" autocomplete="off" spellcheck="false">
                    <p class="ctm-muted">Открыть может только назначенный врач после входа в Норхнейм. Подключение доступно в назначенное время.</p>
                @endif
                @if($assigned_doctor_user)
                    
                    @if($can_doctor_join && !empty($doctor_short_url))
                        <a class="ctm-button" href="{{ $doctor_short_url }}" rel="noreferrer noopener">Войти по короткой ссылке</a>
                    @else
                        <form method="post" action="{{ $doctor_join_url }}">@csrf
                            <button class="ctm-button" type="submit" @disabled(!$can_doctor_join)>Войти в консультацию</button>
                        </form>
                    @endif
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
                    
                    <label class="ctm-label" for="ctm-patient-link">
                        {{ $patient_short_url ? 'Короткая ссылка пациента (clck.ru)' : 'Приглашение пациента (clck.ru временно недоступен)' }}
                    </label>
                    <input class="ctm-input ctm-link" id="ctm-patient-link" type="text" readonly value="{{ $patient_short_url ?: $patient_invitation_url }}" autocomplete="off" spellcheck="false">
                    
                    @if($patient_short_url)
                        <p class="ctm-muted">Пациент войдёт через свой личный кабинет. После переноса времени нужна новая ссылка. Секретный WebRTC-токен в clck.ru не передаётся.</p>
                    @else
                        <p class="ctm-muted">Короткая ссылка требует публичного HTTPS APP_URL и доступного clck.ru. Пока показано обычное защищённое приглашение (не переданное в сокращатель).</p>
                    @endif
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
