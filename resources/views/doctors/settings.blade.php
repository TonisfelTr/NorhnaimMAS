@extends('layouts.doctor')

@section('title', trim($__env->yieldContent('settings-title', 'Настройки')))

@section('assets')
    @parent

    <style>
        :root {
            --settings-primary: #048696;
            --settings-primary-light: #10b7c6;
            --settings-primary-dark: #036b78;
            --settings-bg: #f4f7fb;
            --settings-card: #ffffff;
            --settings-border: #e3e9ef;
            --settings-text: #1f2937;
            --settings-muted: #6b7280;
            --settings-success: #15803d;
            --settings-warning: #b45309;
        }

        .settings-navbar {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--settings-primary) 0%, var(--settings-primary-light) 100%);
            box-shadow: 0 10px 28px rgba(4, 134, 150, .18);
        }

        .settings-navbar__inner {
            min-height: 68px;
            padding: 10px 24px;
            gap: 18px;
        }

        .settings-navbar__brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-right: 10px;
            color: #fff;
            font-size: 17px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .settings-navbar__brand:hover {
            color: #fff;
        }

        .settings-navbar__brand-icon {
            display: inline-flex;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            font-size: 18px;
        }

        .settings-navbar__menu {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .settings-navbar__link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 7px;
            padding: 9px 13px !important;
            border-radius: 12px;
            color: rgba(255, 255, 255, .88) !important;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: .18s ease;
            white-space: nowrap;
        }

        .settings-navbar__link:hover,
        .settings-navbar__link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, .18);
        }

        .settings-navbar__dropdown {
            position: relative;
        }

        .settings-navbar__dropdown-toggle {
            border: 0;
            background: transparent;
        }

        .settings-navbar__dropdown-toggle::after {
            margin-left: 2px;
            vertical-align: .12em;
        }

        .settings-navbar__dropdown-menu {
            min-width: 290px;
            margin-top: 8px !important;
            padding: 8px;
            border: 1px solid #dce7eb;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 22px 56px rgba(15, 23, 42, .18);
        }

        .settings-navbar__dropdown-item {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 10px;
            border-radius: 12px;
            color: #263746;
            text-decoration: none;
            transition: .16s ease;
        }

        .settings-navbar__dropdown-item:hover,
        .settings-navbar__dropdown-item.active {
            background: #eefafb;
            color: #075f6b;
        }

        .settings-navbar__dropdown-icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 16px;
        }

        .settings-navbar__dropdown-copy strong,
        .settings-navbar__dropdown-copy small {
            display: block;
        }

        .settings-navbar__dropdown-copy strong {
            font-size: 12px;
            font-weight: 850;
        }

        .settings-navbar__dropdown-copy small {
            margin-top: 2px;
            color: #7b8794;
            font-size: 10px;
            line-height: 1.35;
        }

        .settings-navbar__context {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            gap: 7px;
            margin-left: auto;
            padding: 7px 12px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .settings-navbar__toggler {
            border: 0;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .18);
        }

        .settings-navbar__toggler:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .28);
        }

        .settings-page {
            min-height: calc(100vh - 68px);
            padding: 26px 28px 44px;
            background: var(--settings-bg);
            color: var(--settings-text);
        }

        .settings-container {
            width: min(1380px, 100%);
            margin: 0 auto;
        }

        .settings-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .settings-page-title {
            margin: 0;
            color: var(--settings-text);
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 820;
            line-height: 1.15;
            letter-spacing: -.035em;
        }

        .settings-page-description {
            max-width: 880px;
            margin: 9px 0 0;
            color: var(--settings-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .settings-type-badge {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border: 1px solid #cae9ed;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .settings-panel {
            overflow: hidden;
            border: 1px solid var(--settings-border);
            border-radius: 20px;
            background: var(--settings-card);
            box-shadow: 0 14px 38px rgba(15, 23, 42, .055);
        }

        .settings-panel + .settings-panel {
            margin-top: 18px;
        }

        .settings-panel__header {
            display: flex;
            min-height: 64px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--settings-border);
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .settings-panel__title-wrap {
            min-width: 0;
        }

        .settings-panel__title {
            margin: 0;
            color: var(--settings-text);
            font-size: 18px;
            font-weight: 800;
        }

        .settings-panel__subtitle {
            margin-top: 4px;
            color: var(--settings-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .settings-panel__body {
            padding: 22px;
        }

        .settings-form-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 17px 18px;
        }

        .settings-col-12 { grid-column: span 12; }
        .settings-col-8 { grid-column: span 8; }
        .settings-col-6 { grid-column: span 6; }
        .settings-col-4 { grid-column: span 4; }

        .settings-label {
            display: block;
            margin-bottom: 7px;
            color: #374151;
            font-size: 12px;
            font-weight: 780;
        }

        .settings-control {
            width: 100%;
            min-height: 44px;
            padding: 9px 12px;
            border: 1px solid #d8e0e5;
            border-radius: 12px;
            background: #fff;
            color: var(--settings-text);
            font-size: 14px;
            transition: .16s ease;
        }

        textarea.settings-control {
            min-height: 112px;
            resize: vertical;
        }

        .settings-control:focus {
            border-color: var(--settings-primary);
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .13);
        }

        .settings-control[readonly] {
            background: #f8fafc;
            color: #64748b;
        }

        .settings-field-hint {
            margin-top: 6px;
            color: #8b95a1;
            font-size: 11px;
            line-height: 1.45;
        }

        .settings-divider {
            height: 1px;
            margin: 22px 0;
            background: #edf1f5;
        }

        .settings-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .settings-button {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 780;
            text-decoration: none;
            cursor: pointer;
            transition: .16s ease;
        }

        .settings-button--primary {
            border-color: var(--settings-primary);
            background: var(--settings-primary);
            color: #fff;
        }

        .settings-button--primary:hover {
            border-color: var(--settings-primary-dark);
            background: var(--settings-primary-dark);
            color: #fff;
        }

        .settings-button--secondary {
            border-color: #d6e0e7;
            background: #fff;
            color: #344054;
        }

        .settings-button--secondary:hover {
            border-color: #c3d0da;
            background: #f8fafc;
            color: #111827;
        }

        .settings-info-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 16px;
            border: 1px solid #d9ecef;
            border-radius: 15px;
            background: #f3fbfc;
            color: #36535a;
            font-size: 12px;
            line-height: 1.55;
        }

        .settings-info-box__icon {
            display: inline-flex;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #e0f4f6;
            color: var(--settings-primary-dark);
            font-size: 15px;
        }

        .settings-empty {
            padding: 42px 24px;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: #f8fafc;
            color: var(--settings-muted);
            text-align: center;
        }

        .settings-empty__icon {
            display: grid;
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            place-items: center;
            border-radius: 18px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 24px;
        }

        .settings-empty h3 {
            margin: 0 0 7px;
            color: var(--settings-text);
            font-size: 18px;
            font-weight: 800;
        }

        .settings-empty p {
            max-width: 600px;
            margin: 0 auto;
            font-size: 13px;
            line-height: 1.55;
        }

        @media (max-width: 1199px) {
            .settings-navbar .navbar-collapse {
                padding: 12px 0 4px;
            }

            .settings-navbar__menu {
                align-items: stretch;
            }

            .settings-navbar__link {
                width: 100%;
            }

            .settings-navbar__context {
                width: fit-content;
                margin: 10px 0 0;
            }
        }

        @media (max-width: 768px) {
            .settings-navbar__inner {
                padding: 10px 16px;
            }

            .settings-page {
                padding: 20px 16px 34px;
            }

            .settings-page-header {
                flex-direction: column;
            }

            .settings-col-8,
            .settings-col-6,
            .settings-col-4 {
                grid-column: span 12;
            }

            .settings-panel__body {
                padding: 18px;
            }

            .settings-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .settings-button {
                width: 100%;
            }
        }
    </style>
@endsection

@section('main')
    <nav class="settings-navbar navbar navbar-expand-xl">
        <div class="container-fluid settings-navbar__inner">
            <a class="settings-navbar__brand" href="{{ url('/doctors/settings') }}">
                <span class="settings-navbar__brand-icon">
                    <i class="bi bi-gear"></i>
                </span>
                <span>Настройки</span>
            </a>

            <button class="navbar-toggler settings-navbar__toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#settingsNavbar"
                    aria-controls="settingsNavbar"
                    aria-expanded="false"
                    aria-label="Открыть навигацию настроек">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="settingsNavbar">
                <ul class="navbar-nav settings-navbar__menu">
                    @if(auth()->user()?->doctor)
                    <li class="nav-item">
                        <a class="settings-navbar__link {{ is_route('doctors.settings.index') ? 'active' : '' }}"
                           href="{{ route('doctors.settings.index') }}">
                            <i class="bi bi-sliders"></i>
                            Общие
                        </a>
                    </li>

                    @endif

                    {{-- Принадлежность стационаров настраивается только из амбулатории.
                         Глобальные права страницы пока сохранены исключительно за debug-тестером. --}}
                    @if(
                        \App\Http\Middleware\ClinicSettingsAccessMiddleware::allows(auth()->user())
                        && auth()->user()?->doctor?->clinic?->clinic_type === \App\Models\Clinic::TYPE_OUTPATIENT
                    )
                        <li class="nav-item">
                            <a class="settings-navbar__link {{ request()->routeIs('doctors.settings.clinic-affiliations.*') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.clinic-affiliations.index') }}">
                                <i class="bi bi-buildings"></i>
                                Принадлежность стационаров
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()?->doctor)
                    {{-- Приоритет у модели $clinic, переданной контроллером текущей страницы. --}}
                    @if(($clinic ?? auth()->user()?->doctor?->clinic)?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                        <li class="nav-item">
                            <a class="settings-navbar__link {{ is_route('doctors.settings.departments') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.departments') }}">
                                <i class="bi bi-diagram-3"></i>
                                Отделения
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="settings-navbar__link {{ is_route('doctors.settings.rooms') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.rooms') }}">
                                <i class="bi bi-door-closed"></i>
                                Палаты и койки
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="settings-navbar__link {{ is_route('doctors.settings.duty-schedule') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.duty-schedule') }}">
                                <i class="bi bi-calendar-check"></i>
                                Дежурства
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="settings-navbar__link {{ is_route('doctors.settings.offices') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.offices') }}">
                                <i class="bi bi-door-open"></i>
                                Кабинеты
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="settings-navbar__link {{ is_route('doctors.settings.districts') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.districts') }}">
                                <i class="bi bi-geo-alt"></i>
                                Участки
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown settings-navbar__dropdown">
                        <button type="button"
                                class="settings-navbar__link settings-navbar__dropdown-toggle dropdown-toggle {{ (is_route('doctors.settings.clinic-schedule') || is_route('doctors.settings.vacation-schedule')) ? 'active' : '' }}"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="bi bi-calendar-week"></i>
                            Графики
                        </button>

                        <div class="dropdown-menu settings-navbar__dropdown-menu">
                            @unless(($clinic ?? auth()->user()?->doctor?->clinic)?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                                <a class="settings-navbar__dropdown-item {{ is_route('doctors.settings.clinic-schedule') ? 'active' : '' }}"
                                   href="{{ route('doctors.settings.clinic-schedule') }}">
                                    <span class="settings-navbar__dropdown-icon">
                                        <i class="bi bi-building-check"></i>
                                    </span>
                                    <span class="settings-navbar__dropdown-copy">
                                        <strong>График клиники</strong>
                                        <small>Рабочая неделя, особые периоды и ограничения</small>
                                    </span>
                                </a>
                            @endunless

                            <a class="settings-navbar__dropdown-item {{ is_route('doctors.settings.vacation-schedule') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.vacation-schedule') }}">
                                <span class="settings-navbar__dropdown-icon">
                                    <i class="bi bi-sun"></i>
                                </span>
                                <span class="settings-navbar__dropdown-copy">
                                    <strong>График отпусков</strong>
                                    <small>Отпуска врачей и медицинского персонала</small>
                                </span>
                            </a>
                        </div>
                    </li>

                    @unless(($clinic ?? auth()->user()?->doctor?->clinic)?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                        <li class="nav-item">
                            <a class="settings-navbar__link {{ is_route('doctors.settings.doctors-schedule') ? 'active' : '' }}"
                               href="{{ route('doctors.settings.doctors-schedule') }}">
                                <i class="bi bi-calendar2-range"></i>
                                Расписание врачей
                            </a>
                        </li>
                    @endunless
                    @endif
                </ul>

                @if(request()->routeIs('doctors.settings.clinic-affiliations.*'))
                    <div class="settings-navbar__context">
                        <i class="bi bi-diagram-3"></i>
                        Структура учреждений
                    </div>
                @elseif(auth()->user()?->doctor)
                    <div class="settings-navbar__context">
                        @if(($clinic ?? auth()->user()?->doctor?->clinic)?->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                            <i class="bi bi-hospital"></i>
                            Стационар
                        @elseif(auth()->user()->doctor->clinic_id)
                            <i class="bi bi-hospital"></i>
                            Клиника
                        @else
                            <i class="bi bi-person-badge"></i>
                            Частная практика
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </nav>

    @yield('sub-main')
@endsection
