@if(!is_authed())
    @php
        $authMode = session('auth_mode', 'login');

        if (session('reset_token')) {
            $authMode = 'reset';
        }

        if (!in_array($authMode, ['login', 'register', 'forgot', 'reset'], true)) {
            $authMode = 'login';
        }
    @endphp

    <div
        class="modal fade authorization-modal"
        id="authorization-block"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        tabindex="-1"
        aria-labelledby="authorization-block__label"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <div class="text-start">
                        <div class="text-uppercase text-secondary small fw-semibold mb-1">
                            Норхнейм
                        </div>
                        <h1 class="modal-title fs-4" id="authorization-block__label">
                            Личный кабинет
                        </h1>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Закрыть"
                    ></button>
                </div>

                <div class="modal-body pt-3">
                    @if(session('auth_success'))
                        <div class="alert alert-success">
                            {{ session('auth_success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Проверьте данные:</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($authMode !== 'reset')
                        <div class="d-flex gap-2 flex-wrap mb-4" role="group" aria-label="Авторизация">
                            <button
                                type="button"
                                class="btn {{ $authMode === 'login' ? 'btn-dark' : 'btn-outline-secondary' }}"
                                data-auth-switch="login"
                            >
                                Вход
                            </button>

                            <button
                                type="button"
                                class="btn {{ $authMode === 'register' ? 'btn-dark' : 'btn-outline-secondary' }}"
                                data-auth-switch="register"
                            >
                                Регистрация
                            </button>
                        </div>
                    @endif

                    <div data-auth-pane="login" @class(['d-none' => $authMode !== 'login'])>
                        <form action="{{ route('actions.login') }}" method="POST">
                            @csrf
                            @recaptcha

                            <div class="mb-3">
                                <label for="authorization-block__email" class="form-label">
                                    Электронная почта
                                </label>
                                <input
                                    class="form-control"
                                    id="authorization-block__email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mb-2">
                                <label for="authorization-block__password" class="form-label">
                                    Пароль
                                </label>
                                <input
                                    class="form-control"
                                    id="authorization-block__password"
                                    type="password"
                                    name="password"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <div class="authorization-login-tools d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div class="authorization-remember">
                                    <x-line-check-box
                                        label="Запомнить меня"
                                        name="remember_me"
                                        :checked="(bool) old('remember_me')"
                                    />
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-link p-0 text-decoration-none"
                                    data-auth-switch="forgot"
                                >
                                    Забыли пароль?
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-dark" type="submit">
                                    Войти
                                </button>

                                <button
                                    class="btn btn-light"
                                    type="button"
                                    data-bs-dismiss="modal"
                                >
                                    Закрыть
                                </button>
                            </div>
                        </form>
                    </div>

                    <div data-auth-pane="register" @class(['d-none' => $authMode !== 'register'])>
                        <div class="mb-3 text-secondary">
                            Создаётся учётная запись пациента. Привязку к существующей медицинской
                            карте выполним отдельно после подтверждения личности.
                        </div>

                        <form action="{{ route('actions.register') }}" method="POST">
                            @csrf
                            @recaptcha

                            <div class="mb-3">
                                <label for="authorization-register__login" class="form-label">
                                    Имя или логин
                                </label>
                                <input
                                    class="form-control"
                                    id="authorization-register__login"
                                    type="text"
                                    name="login"
                                    value="{{ old('login') }}"
                                    autocomplete="username"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="authorization-register__email" class="form-label">
                                    Электронная почта
                                </label>
                                <input
                                    class="form-control"
                                    id="authorization-register__email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="authorization-register__password" class="form-label">
                                        Пароль
                                    </label>
                                    <input
                                        class="form-control"
                                        id="authorization-register__password"
                                        type="password"
                                        name="password"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="authorization-register__password-confirmation" class="form-label">
                                        Повторите пароль
                                    </label>
                                    <input
                                        class="form-control"
                                        id="authorization-register__password-confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-check mt-3 mb-4">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    value="1"
                                    id="authorization-register__privacy"
                                    name="accept_privacy"
                                    @checked(old('accept_privacy'))
                                    required
                                >
                                <label class="form-check-label" for="authorization-register__privacy">
                                    Я согласен с
                                    <a href="{{ route('main.policy') }}" target="_blank" rel="noopener">
                                        политикой конфиденциальности
                                    </a>
                                </label>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-dark" type="submit">
                                    Зарегистрироваться
                                </button>

                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    data-auth-switch="login"
                                >
                                    Уже есть аккаунт
                                </button>
                            </div>
                        </form>
                    </div>

                    <div data-auth-pane="forgot" @class(['d-none' => $authMode !== 'forgot'])>
                        <h2 class="h5 mb-2">Восстановление пароля</h2>
                        <p class="text-secondary mb-4">
                            Укажите email. Если он зарегистрирован, мы отправим ссылку для смены пароля.
                        </p>

                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
                            @recaptcha

                            <div class="mb-4">
                                <label for="authorization-forgot__email" class="form-label">
                                    Электронная почта
                                </label>
                                <input
                                    class="form-control"
                                    id="authorization-forgot__email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-dark" type="submit">
                                    Отправить ссылку
                                </button>

                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    data-auth-switch="login"
                                >
                                    Вернуться ко входу
                                </button>
                            </div>
                        </form>
                    </div>

                    <div data-auth-pane="reset" @class(['d-none' => $authMode !== 'reset'])>
                        <h2 class="h5 mb-2">Новый пароль</h2>
                        <p class="text-secondary mb-4">
                            Задайте новый пароль для своей учётной записи.
                        </p>

                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf
                            @recaptcha

                            <input
                                type="hidden"
                                name="token"
                                value="{{ session('reset_token', old('token')) }}"
                            >

                            <div class="mb-3">
                                <label for="authorization-reset__email" class="form-label">
                                    Электронная почта
                                </label>
                                <input
                                    class="form-control"
                                    id="authorization-reset__email"
                                    type="email"
                                    name="email"
                                    value="{{ session('reset_email', old('email')) }}"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="authorization-reset__password" class="form-label">
                                        Новый пароль
                                    </label>
                                    <input
                                        class="form-control"
                                        id="authorization-reset__password"
                                        type="password"
                                        name="password"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="authorization-reset__password-confirmation" class="form-label">
                                        Повторите пароль
                                    </label>
                                    <input
                                        class="form-control"
                                        id="authorization-reset__password-confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-dark" type="submit">
                                    Сохранить новый пароль
                                </button>

                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    data-auth-switch="login"
                                >
                                    Вернуться ко входу
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('authorization-block');

            if (!modalElement) {
                return;
            }

            const switchButtons = modalElement.querySelectorAll('[data-auth-switch]');
            const panes = modalElement.querySelectorAll('[data-auth-pane]');

            function setAuthMode(mode) {
                panes.forEach(function (pane) {
                    pane.classList.toggle(
                        'd-none',
                        pane.dataset.authPane !== mode
                    );
                });

                switchButtons.forEach(function (button) {
                    const active = button.dataset.authSwitch === mode;

                    if (button.closest('[role="group"]')) {
                        button.classList.toggle('btn-dark', active);
                        button.classList.toggle('btn-outline-secondary', !active);
                    }
                });
            }

            switchButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setAuthMode(this.dataset.authSwitch);
                });
            });

            setAuthMode(@json($authMode));

            @if(session('open_modal') == 'authorization-block')
                const modal = bootstrap.Modal.getOrCreateInstance(
                    modalElement,
                    {
                        backdrop: 'static',
                        keyboard: false
                    }
                );

                modal.show();
            @endif
        });
    </script>
@endif
