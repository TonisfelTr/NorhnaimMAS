<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Панель пациента СМА Норхнейм</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Montserrat:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/sass/patients.sass'])
    @yield('assets')
</head>

<body class="patient-panel-body">
    <div
        id="patientSidebarBackdrop"
        class="patient-sidebar-backdrop"
        aria-hidden="true"
    ></div>

    <header class="patient-mobile-header">
        <button
            type="button"
            class="patient-mobile-header__menu"
            id="patientSidebarOpen"
            aria-label="Открыть меню"
            aria-controls="patientSidebar"
            aria-expanded="false"
        >
            <i class="bi bi-list"></i>
        </button>

        <a
            href="{{ route('patients.dashboard') }}"
            class="patient-mobile-header__brand"
        >
            <span class="patient-mobile-header__symbol">
                <i class="bi bi-heart-pulse-fill"></i>
            </span>
            <span>
                Норхнейм
                <small>Панель пациента</small>
            </span>
        </a>
    </header>

    <aside
        id="patientSidebar"
        class="patient-sidebar"
        aria-label="Навигация панели пациента"
    >
        <div class="patient-sidebar__top">
            <a
                class="patient-sidebar__brand"
                href="{{ route('patients.dashboard') }}"
            >
                <span class="patient-sidebar__brand-icon">
                    <i class="bi bi-heart-pulse-fill"></i>
                </span>

                <span class="patient-sidebar__brand-copy">
                    <strong>Норхнейм</strong>
                    <small>ПАНЕЛЬ ПАЦИЕНТА</small>
                </span>
            </a>

            <button
                type="button"
                id="patientSidebarClose"
                class="patient-sidebar__close"
                aria-label="Закрыть меню"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="patient-sidebar__scroll">
            <div class="patient-sidebar__group">
                <div class="patient-sidebar__heading">
                    <span>Моё лечение</span>
                </div>

                <nav aria-label="Основные разделы">
                    <ul class="patient-sidebar__nav">
                        <li>
                            <a
                                href="{{ route('patients.dashboard') }}"
                                @class(['patient-sidebar__link', 'is-active' => request()->routeIs('patients.dashboard')])
                                @if(request()->routeIs('patients.dashboard')) aria-current="page" @endif
                            >
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-grid-1x2-fill"></i>
                                </span>
                                <span>Обзор</span>
                            </a>
                        </li>
                        <li>
                            <a
                                href="{{ route('patients.profile.index') }}"
                                @class(['patient-sidebar__link', 'is-active' => request()->routeIs('patients.profile.*')])
                                @if(request()->routeIs('patients.profile.*')) aria-current="page" @endif
                            >
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-person-vcard"></i>
                                </span>
                                <span>Мой профиль</span>
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('patients.calendar') }}"
                                @class(['patient-sidebar__link', 'is-active' => request()->routeIs('patients.calendar')])
                                @if(request()->routeIs('patients.calendar')) aria-current="page" @endif
                            >
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-calendar3"></i>
                                </span>
                                <span>Календарь</span>
                            </a>
                        </li>


                        <li>
                            <span class="patient-sidebar__link is-disabled">
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-capsule-pill"></i>
                                </span>
                                <span>Назначения</span>
                                <small>скоро</small>
                            </span>
                        </li>

                        <li>
                            <span class="patient-sidebar__link is-disabled">
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-activity"></i>
                                </span>
                                <span>Исследования</span>
                                <small>скоро</small>
                            </span>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="patient-sidebar__group">
                <div class="patient-sidebar__heading">
                    <span>Сервисы</span>
                </div>

                <nav aria-label="Сервисы пациента">
                    <ul class="patient-sidebar__nav">
                        <li>
                            <span class="patient-sidebar__link is-disabled">
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-ui-checks-grid"></i>
                                </span>
                                <span>Тестирования</span>
                                <small>скоро</small>
                            </span>
                        </li>

                        <li>
                            <span class="patient-sidebar__link is-disabled">
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-file-earmark-medical"></i>
                                </span>
                                <span>Документы</span>
                                <small>скоро</small>
                            </span>
                        </li>

                        <li>
                            <span class="patient-sidebar__link is-disabled">
                                <span class="patient-sidebar__link-icon">
                                    <i class="bi bi-camera-video"></i>
                                </span>
                                <span>Телемедицина</span>
                                <small>скоро</small>
                            </span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="patient-sidebar__footer">
            <div class="patient-sidebar__account">
                <span class="patient-sidebar__avatar">
                    {{ mb_strtoupper(mb_substr(auth()->user()->login ?? 'П', 0, 1)) }}
                </span>

                <span class="patient-sidebar__account-copy">
                    <strong>
                        {{ $patient?->name ?? auth()->user()->login }}
                    </strong>
                    <small>
                        {{ $patient ? 'Моя медицинская карта' : 'Учётная запись пациента' }}
                    </small>
                </span>
            </div>

            <div class="patient-sidebar__footer-actions">
                <a href="{{ route('main.index') }}">
                    <i class="bi bi-box-arrow-up-right"></i>
                    На сайт
                </a>

                <a href="{{ route('actions.logout') }}">
                    <i class="bi bi-box-arrow-right"></i>
                    Выйти
                </a>
            </div>
        </div>
    </aside>

    <main class="patient-main">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const sidebar = document.getElementById('patientSidebar');
            const backdrop = document.getElementById('patientSidebarBackdrop');
            const openButton = document.getElementById('patientSidebarOpen');
            const closeButton = document.getElementById('patientSidebarClose');

            function openSidebar() {
                body.classList.add('patient-sidebar-open');
                openButton?.setAttribute('aria-expanded', 'true');
                backdrop?.setAttribute('aria-hidden', 'false');
            }

            function closeSidebar() {
                body.classList.remove('patient-sidebar-open');
                openButton?.setAttribute('aria-expanded', 'false');
                backdrop?.setAttribute('aria-hidden', 'true');
            }

            openButton?.addEventListener('click', openSidebar);
            closeButton?.addEventListener('click', closeSidebar);
            backdrop?.addEventListener('click', closeSidebar);

            window.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            sidebar?.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
