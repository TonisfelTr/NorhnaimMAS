<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(request('api_token'))
        <meta name="x-test-api-token" content="{{ request('api_token') }}">
        <script>
            window.TEST_API = { token: document.querySelector('meta[name="x-test-api-token"]').content };
        </script>
    @endif
    <title>@yield('title') - Панель врача СМА Норхнейм</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite(['resources/sass/doctors.sass'])
    @yield('assets')
    @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY))
        <style>
            .doctor-sidebar--hospital-login { opacity:.43; pointer-events:none; filter:grayscale(.55); }
            .nh-login-as-banner { display:flex; align-items:center; justify-content:space-between;
                flex-wrap:wrap; gap:14px; padding:13px 22px; border-bottom:1px solid #bbe8ed;
                color:#124456; background:linear-gradient(110deg,#e4f7f8,#f6fcfd); }
            .nh-login-as-banner__info { display:flex; align-items:center; gap:12px; min-width:0; }
            .nh-login-as-banner__icon { display:grid;place-items:center;width:39px;height:39px;border-radius:12px;
                background:#cceff1;color:#087f91;font-size:19px;flex-shrink:0; }
            .nh-login-as-banner strong { display:block; font-size:13px; }
            .nh-login-as-banner small { color:#66838b; font-size:11px; }
            .nh-login-as-banner__exit {display:inline-flex;align-items:center;gap:9px; padding:9px 15px;
                border-radius:10px;border:1px solid #188e9b;background:#078fa1;color:#fff;
                font-size:12px;font-weight:750;cursor:pointer;}
            .nh-login-as-banner__exit:hover {background:#037d8d;}
            .nh-login-as-banner { min-height:58px; flex-wrap:nowrap; padding:9px 18px; }
            .nh-login-as-banner__info { flex:1 1 auto; min-width:0; }
            .nh-login-as-banner__info > div { min-width:0; }
            .nh-login-as-banner__info strong,
            .nh-login-as-banner__info small { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .nh-login-as-banner #nhHospitalLoginExitForm { margin:0; flex:0 0 auto; }
            @media (max-width:680px) {
                .nh-login-as-banner {flex-wrap:wrap; padding:9px 12px; gap:8px;}
                .nh-login-as-banner #nhHospitalLoginExitForm { margin-left:48px; }
            }
            .nh-native-preview-note { margin:12px 22px 4px;padding:12px 16px;border-radius:11px;
                border:1px solid #efd9ab;background:#fff9ec;color:#775a27;font-size:12px; }

        </style>
    @endif

</head>
<body class="d-flex{{ session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY) ? ' hospital-login-as-active' : '' }}">

{{-- Мобильная шапка навигации. Основное содержимое страниц не затрагивает. --}}
<div class="doctor-mobile-navbar">
    <button type="button"
            id="doctorSidebarOpen"
            class="doctor-mobile-navbar__menu"
            @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)) disabled @endif
            aria-label="Открыть меню навигации"
            aria-controls="doctorSidebar"
            aria-expanded="false">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <div class="doctor-mobile-navbar__identity" aria-label="Панель врача СМА Норхнейм">
        <span class="doctor-mobile-navbar__symbol"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i></span>
        <span class="doctor-mobile-navbar__title">Норхнейм <small>Панель врача</small></span>
    </div>
    <span class="doctor-mobile-navbar__mode">
        @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)
            || auth()->user()?->doctor?->clinic?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
            Стационар (настройка)
        @else
            Амбулатория
        @endif
    </span>
</div>

<div id="doctorSidebarBackdrop" class="doctor-sidebar__backdrop" aria-hidden="true"></div>

<aside id="doctorSidebar" class="doctor-sidebar{{ session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY) ? ' doctor-sidebar--hospital-login' : '' }}"
       @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)) inert aria-disabled="true" @endif
       aria-label="Основная навигация панели врача">
    <div class="doctor-sidebar__top">
        <a class="doctor-sidebar__brand" href="{{ route('doctors.main') }}" title="На главную страницу панели врача">
            <span class="doctor-sidebar__brand-icon"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i></span>
            <span class="doctor-sidebar__brand-copy">
                <strong>Норхнейм</strong>
                <small>ПАНЕЛЬ ВРАЧА</small>
            </span>
        </a>

        <button type="button" id="doctorSidebarCollapse"
                class="doctor-sidebar__collapse"
                aria-label="Свернуть навигацию"
                aria-controls="doctorSidebar"
                aria-expanded="true"
                title="Свернуть навигацию">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" id="doctorSidebarClose"
                class="doctor-sidebar__close"
                aria-label="Закрыть меню навигации">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>

    <div class="doctor-sidebar__scroll">
        <div class="doctor-sidebar__group">
            <div class="doctor-sidebar__heading"><span>Рабочее пространство</span></div>
            <nav aria-label="Основные разделы">
                <ul class="doctor-sidebar__nav">
                    <li>
                        <a href="{{ route('doctors.main') }}"
                           class="doctor-sidebar__link {{ request()->routeIs('doctors.main') ? 'is-active' : '' }}"
                           title="Статистика" aria-label="Статистика"
                           @if(request()->routeIs('doctors.main')) aria-current="page" @endif>
                            <span class="doctor-sidebar__link-icon"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
                            <span class="doctor-sidebar__link-text">Статистика</span>
                            <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('doctors.reception.index') }}"
                           class="doctor-sidebar__link {{ request()->routeIs('doctors.reception.*') ? 'is-active' : '' }}"
                           title="Регистратура" aria-label="Регистратура"
                           @if(request()->routeIs('doctors.reception.*')) aria-current="page" @endif>
                            <span class="doctor-sidebar__link-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                            <span class="doctor-sidebar__link-text">Регистратура</span>
                            <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="doctor-sidebar__group">
            <div class="doctor-sidebar__heading"><span>Медицинские сервисы</span></div>
            <nav aria-label="Медицинские сервисы">
                <ul class="doctor-sidebar__nav">
                    <li>
                        <a href="{{ route('doctors.tests.index') }}"
                           class="doctor-sidebar__link {{ request()->routeIs('doctors.tests.*') ? 'is-active' : '' }}"
                           title="Тесты" aria-label="Тесты"
                           @if(request()->routeIs('doctors.tests.*')) aria-current="page" @endif>
                            <span class="doctor-sidebar__link-icon"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i></span>
                            <span class="doctor-sidebar__link-text">Тесты</span>
                            <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('doctors.analyses.index') }}"
                           class="doctor-sidebar__link {{ request()->routeIs('doctors.analyses.*') ? 'is-active' : '' }}"
                           title="Анализы" aria-label="Анализы"
                           @if(request()->routeIs('doctors.analyses.*')) aria-current="page" @endif>
                            <span class="doctor-sidebar__link-icon"><i class="bi bi-activity" aria-hidden="true"></i></span>
                            <span class="doctor-sidebar__link-text">Анализы</span>
                            <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                        </a>
                    </li>
                    @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)
            || auth()->user()?->doctor?->clinic?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                        <li>
                            <a href="{{ route('doctors.inpatient.discharges.index') }}"
                               class="doctor-sidebar__link {{ request()->routeIs('doctors.inpatient.*') ? 'is-active' : '' }}"
                               title="Стационар" aria-label="Стационар"
                               @if(request()->routeIs('doctors.inpatient.*')) aria-current="page" @endif>
                                <span class="doctor-sidebar__link-icon"><i class="bi bi-hospital-fill" aria-hidden="true"></i></span>
                                <span class="doctor-sidebar__link-text">Стационар</span>
                                <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('doctors.prescriptions.index') }}"
                               class="doctor-sidebar__link {{ request()->routeIs('doctors.prescriptions.*') ? 'is-active' : '' }}"
                               title="Выписки" aria-label="Выписки"
                               @if(request()->routeIs('doctors.prescriptions.*')) aria-current="page" @endif>
                                <span class="doctor-sidebar__link-icon"><i class="bi bi-file-earmark-medical-fill" aria-hidden="true"></i></span>
                                <span class="doctor-sidebar__link-text">Выписки</span>
                                <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

        @if(\App\Http\Middleware\ClinicSettingsAccessMiddleware::allows(auth()->user()))
        <div class="doctor-sidebar__group">
            <div class="doctor-sidebar__heading"><span>Управление</span></div>
            <nav aria-label="Управление и настройки">
                <ul class="doctor-sidebar__nav">
                    <li>
                        <a href="{{ route('doctors.settings.index') }}"
                           class="doctor-sidebar__link {{ request()->routeIs('doctors.settings.*') ? 'is-active' : '' }}"
                           title="Настройки" aria-label="Настройки"
                           @if(request()->routeIs('doctors.settings.*')) aria-current="page" @endif>
                            <span class="doctor-sidebar__link-icon"><i class="bi bi-sliders2-vertical" aria-hidden="true"></i></span>
                            <span class="doctor-sidebar__link-text">Настройки</span>
                            <span class="doctor-sidebar__link-active-dot" aria-hidden="true"></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>

    <div class="doctor-sidebar__footer">
        <div class="doctor-sidebar__mode-card" aria-label="Режим клиники">
            <span class="doctor-sidebar__mode-icon"><i class="bi bi-hospital" aria-hidden="true"></i></span>
            <span class="doctor-sidebar__mode-copy">
                <small>Режим клиники</small>
                <strong>
                    @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)
            || auth()->user()?->doctor?->clinic?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                        Стационар (настройка)
                    @else
                        Амбулатория
                    @endif
                </strong>
            </span>
            <span class="doctor-sidebar__mode-indicator" aria-hidden="true"></span>
        </div>

        <div class="dropdown dropup doctor-sidebar__account-dropdown">
            <button class="doctor-sidebar__account" type="button"
                    id="doctorSidebarAccount" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Меню пользователя {{ auth()->user()->login }}"
                    title="Аккаунт: {{ auth()->user()->login }}">
                <span class="doctor-sidebar__avatar">{{ mb_strtoupper(mb_substr(auth()->user()->login ?? 'В', 0, 1)) }}</span>
                <span class="doctor-sidebar__account-copy">
                    <strong>{{ auth()->user()->login }}</strong>
                    <small>Личный кабинет</small>
                </span>
                <i class="doctor-sidebar__account-chevron bi bi-three-dots" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu doctor-sidebar__dropdown" aria-labelledby="doctorSidebarAccount">
                <li><a class="dropdown-item" href="{{ route('main.index') }}"><i class="bi bi-house-door" aria-hidden="true"></i> Главная</a></li>
                @if(\App\Http\Middleware\ClinicSettingsAccessMiddleware::allows(auth()->user()))
                <li><a class="dropdown-item" href="{{ route('doctors.settings.index') }}"><i class="bi bi-gear" aria-hidden="true"></i> Настройки</a></li>
                @endif
                <li><a class="dropdown-item" href="#"><i class="bi bi-person" aria-hidden="true"></i> Профиль</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item doctor-sidebar__logout" href="{{ route('actions.logout') }}"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Выход</a></li>
            </ul>
        </div>
    </div>
</aside>

{{-- Содержимое страниц не меняем. --}}
<div class="main-content flex-grow-1">
    @if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY))
        <div class="nh-login-as-banner" role="status" aria-label="Временный режим стационара">
            <div class="nh-login-as-banner__info">
                <span class="nh-login-as-banner__icon" aria-hidden="true"><i class="bi bi-hospital"></i></span>
                <div>
                    <strong>Настройка стационара: {{ session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)['hospital_name'] ?? 'Стационар' }}</strong>
                    <small>Исходная организация: {{ session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)['origin_name'] ?? 'Амбулатория' }} · медицинские разделы заблокированы</small>
                </div>
            </div>
            <form id="nhHospitalLoginExitForm" method="POST" action="{{ route('doctors.hospital-impersonation.leave') }}">
                @csrf
                <button type="submit" class="nh-login-as-banner__exit" onclick="try { sessionStorage.removeItem('norhnaim:hospital-login-tab'); } catch (_) {}">
                    <i class="bi bi-arrow-return-left" aria-hidden="true"></i> Вернуться в амбулаторию
                </button>
            </form>
        </div>
        @if(request()->routeIs('doctors.settings.departments', 'doctors.settings.rooms'))
            <div class="nh-native-preview-note" role="status">
                <i class="bi bi-eye" aria-hidden="true"></i>
                Вы просматриваете <strong>существующую страницу настроек выбранного стационара</strong>.
                Пока это безопасный предпросмотр: создание, изменение, удаление и размещение пациентов
                через оригинальные формы заблокированы на сервере. Для редактирования используйте
                вкладки предыдущего рабочего окна до завершения адаптации штатных контроллеров.
            </div>
        @endif
    @endif
    @yield('main')
</div>
@stack('scripts')
@if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY)
    && request()->routeIs('doctors.settings.departments', 'doctors.settings.rooms'))
<script>
    document.addEventListener('submit', function (event) {
        // Кнопка возврата из режима должна оставаться рабочей.
        if (event.target?.id === 'nhHospitalLoginExitForm') return;
        if (event.target && String(event.target.method || '').toUpperCase() !== 'GET') {
            event.preventDefault();
            event.stopImmediatePropagation();
            window.alert('Это предпросмотр оригинальных настроек. Сохранение пока доступно в предыдущем окне настроек стационара.');
        }
    }, true);
</script>
@endif
@if(session(\App\Http\Controllers\Doctors\HospitalImpersonationController::SESSION_KEY))
    <script>
        (() => {
            const expectedToken = @json(session('norhnaim.hospital_impersonation.token', ''));
            const storageKey = 'norhnaim:hospital-login-tab';
            const exitForm = document.getElementById('nhHospitalLoginExitForm');
            try {
                if (sessionStorage.getItem(storageKey) !== expectedToken) {
                    // Вкладка была закрыта или открылась новая: восстанавливаем амбулаторию.
                    exitForm?.requestSubmit();
                    return;
                }
            } catch (error) { exitForm?.requestSubmit(); return; }
            const exitTo = url => {
                if (!url) return;
                try {
                    const target = new URL(url, window.location.origin);
                    if (target.origin !== window.location.origin) return;
                    sessionStorage.removeItem(storageKey);
                    window.location.assign(target.pathname + target.search + target.hash);
                } catch (_) { /* навигация остаётся ручной */ }
            };
            const originalFetch = window.fetch.bind(window);
            window.fetch = async function (...args) {
                const response = await originalFetch(...args);
                exitTo(response.headers.get('X-Norhnaim-Exit-To'));
                return response;
            };
            if (window.jQuery) {
                window.jQuery(document).ajaxComplete((_event, xhr) => {
                    exitTo(xhr.getResponseHeader('X-Norhnaim-Exit-To'));
                });
            }
        })();
    </script>
@endif


<script>
    (() => {
        const sidebar = document.getElementById('doctorSidebar');
        const openButton = document.getElementById('doctorSidebarOpen');
        const closeButton = document.getElementById('doctorSidebarClose');
        const collapseButton = document.getElementById('doctorSidebarCollapse');
        const backdrop = document.getElementById('doctorSidebarBackdrop');
        const mobileQuery = window.matchMedia('(max-width: 991.98px)');
        const storageKey = 'norkheim:doctor-sidebar-collapsed';
        let previousFocus = null;

        if (!sidebar || !openButton || !closeButton || !collapseButton || !backdrop) return;

        try {
            sidebar.classList.toggle('is-collapsed', localStorage.getItem(storageKey) === '1');
        } catch (error) {
            // Доступ к localStorage может быть ограничен настройками браузера.
        }

        const updateCollapseButton = () => {
            const collapsed = sidebar.classList.contains('is-collapsed');
            const label = collapsed ? 'Развернуть навигацию' : 'Свернуть навигацию';
            collapseButton.setAttribute('aria-label', label);
            collapseButton.setAttribute('title', label);
            collapseButton.setAttribute('aria-expanded', String(!collapsed));
        };

        const syncSidebarAccessibility = () => {
            const hidden = mobileQuery.matches && !sidebar.classList.contains('is-open');
            // При временной настройке стационара меню остаётся inert
            // даже на десктопе: не только визуально, но и для клавиатуры.
            const hospitalEditor = sidebar.classList.contains('doctor-sidebar--hospital-login');
            sidebar.inert = hidden || hospitalEditor;
            sidebar.setAttribute('aria-hidden', String(hidden || hospitalEditor));
        };

        const closeMobileMenu = (restoreFocus = true) => {
            if (!sidebar.classList.contains('is-open')) return;
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-visible');
            openButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('doctor-sidebar-open');
            syncSidebarAccessibility();
            if (restoreFocus && mobileQuery.matches) {
                (previousFocus?.isConnected ? previousFocus : openButton).focus();
            }
        };

        openButton.addEventListener('click', () => {
            if (!mobileQuery.matches) return;
            previousFocus = document.activeElement;
            sidebar.classList.add('is-open');
            backdrop.classList.add('is-visible');
            openButton.setAttribute('aria-expanded', 'true');
            document.body.classList.add('doctor-sidebar-open');
            syncSidebarAccessibility();
            closeButton.focus();
        });
        closeButton.addEventListener('click', () => closeMobileMenu());
        backdrop.addEventListener('click', () => closeMobileMenu());

        collapseButton.addEventListener('click', () => {
            if (mobileQuery.matches) return;
            const collapsed = sidebar.classList.toggle('is-collapsed');
            try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (error) { /* optional */ }
            updateCollapseButton();
        });

        sidebar.querySelectorAll('.doctor-sidebar__nav a').forEach(link => {
            link.addEventListener('click', () => closeMobileMenu(false));
        });

        document.addEventListener('keydown', event => {
            if (!mobileQuery.matches || !sidebar.classList.contains('is-open')) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                closeMobileMenu();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')]
                .filter(el => el.getClientRects().length > 0);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        mobileQuery.addEventListener('change', () => {
            closeMobileMenu(false);
            syncSidebarAccessibility();
        });
        updateCollapseButton();
        syncSidebarAccessibility();
    })();
</script>
</body>
</html>
