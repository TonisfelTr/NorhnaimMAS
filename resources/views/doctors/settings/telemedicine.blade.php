@extends('doctors.settings')

@section('settings-title', 'Телемедицина')

@section('assets')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/css/clinic_telemedicine_settings.css') }}">
    <script src="{{ asset('assets/js/clinic_telemedicine_settings.js') }}" defer></script>
@endsection

@section('sub-main')
    <main class="settings-page tm-settings">
        <div class="settings-container">
            <header class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">Телемедицина</h1>
                    <p class="settings-page-description">Способ подключения к дистанционным консультациям для {{ $telemedicineForm['clinic_name'] }}.</p>
                </div>
                <a href="{{ $telemedicineForm['general_url'] }}" class="settings-button settings-button--secondary">
                    <i class="bi bi-sliders" aria-hidden="true"></i> Общие настройки
                </a>
            </header>

            @if (session('success'))
                <div class="tm-settings__notice tm-settings__notice--success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="tm-settings__notice tm-settings__notice--error" role="alert">
                    <strong>Не удалось сохранить настройки</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <p>Если вы вводили новый токен, введите его повторно.</p>
                </div>
            @endif

            @if ($telemedicineForm['setup_errors'])
                <div class="tm-settings__notice" role="status">
                    <strong>Состояние сохранённых настроек</strong>
                    <ul>
                        @foreach ($telemedicineForm['setup_errors'] as $setupError)
                            <li>{{ $setupError }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $telemedicineForm['submit_url'] }}" data-telemedicine-settings>
                @csrf
                <section class="settings-panel">
                    <div class="settings-panel__header">
                        <div>
                            <h2 class="settings-panel__title">Подключение к консультациям</h2>
                            <p class="settings-panel__subtitle">Выберите способ, который будет использоваться для новых подключений.</p>
                        </div>
                    </div>
                    <div class="settings-panel__body tm-settings__stack">
                        <div class="tm-settings__field">
                            <label for="tmProvider" class="settings-label">Способ видеосвязи</label>
                            <select id="tmProvider" name="provider" class="settings-control" data-tm-provider required>
                                <option value="">Выберите способ</option>
                                <option value="webrtc" @selected($telemedicineForm['provider'] === 'webrtc')>Собственная видеосвязь WebRTC</option>
                                <option value="telemost" @selected($telemedicineForm['provider'] === 'telemost')>Яндекс Телемост</option>
                            </select>
                            @error('provider')<div class="tm-settings__field-error">{{ $message }}</div>@enderror
                        </div>

                        <section class="tm-settings__provider" data-tm-panel="webrtc" @if (!$telemedicineForm['show_webrtc']) hidden @endif>
                            <h3><i class="bi bi-camera-video" aria-hidden="true"></i> Собственная видеосвязь</h3>
                            <p class="settings-field-hint">Используется существующий сервер видеосвязи клиники. Пациент и врач подключаются по отдельным ссылкам консультации.</p>
                            <div class="tm-settings__field">
                                <label for="tmWebrtcUrl" class="settings-label">HTTPS-адрес страницы подключения</label>
                                <input id="tmWebrtcUrl" name="webrtc_url" type="text" inputmode="url" class="settings-control"
                                       value="{{ $telemedicineForm['webrtc_url'] }}" maxlength="2048"
                                       placeholder="https://video.example.ru/demo" autocomplete="url"
                                       aria-describedby="tmWebrtcHelp">
                                <div id="tmWebrtcHelp" class="settings-field-hint">Полный адрес страницы видеосвязи без параметров и фрагмента. Данные конкретной консультации добавляются при подключении.</div>
                                @error('webrtc_url')<div class="tm-settings__field-error">{{ $message }}</div>@enderror
                            </div>
                        </section>

                        <section class="tm-settings__provider" data-tm-panel="telemost" @if (!$telemedicineForm['show_telemost']) hidden @endif>
                            <h3><i class="bi bi-camera-video" aria-hidden="true"></i> Яндекс Телемост</h3>
                            <div class="tm-settings__field">
                                <label for="tmTelemostMode" class="settings-label">Создание встреч</label>
                                <select id="tmTelemostMode" name="telemost_mode" class="settings-control" data-tm-telemost-mode required>
                                    <option value="api" @selected($telemedicineForm['telemost_mode'] === 'api')>Автоматически через API</option>
                                    <option value="manual" @selected($telemedicineForm['telemost_mode'] === 'manual')>Добавлять ссылку вручную</option>
                                </select>
                                @error('telemost_mode')<div class="tm-settings__field-error">{{ $message }}</div>@enderror
                            </div>

                            <div data-tm-api-fields @if (!$telemedicineForm['show_api']) hidden @endif class="tm-settings__stack">
                                <p class="settings-field-hint">Для автоматического создания встреч требуется действующий доступ к API Телемоста.</p>
                                <div class="tm-settings__token-state">
                                    @if ($telemedicineForm['has_telemost_token'])
                                        <i class="bi bi-shield-check" aria-hidden="true"></i> Токен сохранён
                                    @else
                                        <i class="bi bi-key" aria-hidden="true"></i> Токен ещё не сохранён
                                    @endif
                                </div>
                                <div class="tm-settings__field">
                                    <label for="tmTelemostToken" class="settings-label">Новый OAuth-токен</label>
                                    <input id="tmTelemostToken" name="telemost_token" type="password" class="settings-control"
                                           maxlength="8192" autocomplete="new-password" spellcheck="false" aria-describedby="tmTokenHelp">
                                    <div id="tmTokenHelp" class="settings-field-hint">Оставьте поле пустым, чтобы сохранить прежний токен. Сохранённое значение не показывается.</div>
                                    @error('telemost_token')<div class="tm-settings__field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <input type="hidden" name="clear_telemost_token" value="0">
                            <label class="tm-settings__check" for="tmClearToken">
                                <input id="tmClearToken" name="clear_telemost_token" type="checkbox" value="1" @checked($telemedicineForm['clear_telemost_token'])>
                                <span>Удалить сохранённый токен при сохранении</span>
                            </label>
                            @error('clear_telemost_token')<div class="tm-settings__field-error">{{ $message }}</div>@enderror
                            <p class="settings-field-hint" data-tm-manual-help @if ($telemedicineForm['show_api']) hidden @endif>Ссылку на отдельную встречу указывают в записи консультации. Один общий адрес для всех пациентов здесь не сохраняется.</p>
                        </section>

                        <div class="tm-settings__timezone">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            <div>
                                <strong>Часовой пояс клиники: {{ $telemedicineForm['timezone'] }}</strong>
                                <p class="settings-field-hint">Изменяется в <a href="{{ $telemedicineForm['general_url'] }}#clinicTimezone">общих настройках клиники</a>.</p>
                            </div>
                        </div>
                    </div>
                    <footer class="tm-settings__footer">
                        <span class="settings-field-hint">Формат «Телемедицина» включён в общих настройках.</span>
                        <button type="submit" class="settings-button settings-button--primary">
                            <i class="bi bi-check2" aria-hidden="true"></i> Сохранить настройки
                        </button>
                    </footer>
                </section>
            </form>
        </div>
    </main>
@endsection
