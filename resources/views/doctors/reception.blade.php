@extends('layouts.doctor')
@inject('receptionNavigation', 'App\Support\ReceptionNavigation')
@section('main')
    <style>
        .reception-navbar {
            margin: 0 0 0 0;
            padding: 0;
            background: linear-gradient(135deg, #048696 0%, #10b7c6 100%);
            box-shadow: 0 10px 28px rgba(4, 134, 150, 0.18);
        }

        .reception-navbar__inner {
            min-height: 64px;
            padding: 10px 24px;
        }

        .reception-navbar__brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-right: 12px;
            color: #ffffff;
            font-size: 17px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .reception-navbar__brand:hover {
            color: #ffffff;
        }

        .reception-navbar__brand-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            font-size: 18px;
        }

        .reception-navbar__menu {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-right: 14px;
        }

        .reception-navbar__mode {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border: 1px solid rgba(255, 255, 255, .23);
            border-radius: 999px;
            background: rgba(255, 255, 255, .13);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
            margin-right: 14px;
        }

        .reception-navbar__link {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 11px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .reception-navbar__link:hover,
        .reception-navbar__link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
        }

        .reception-navbar__link i {
            font-size: 15px;
        }

        .reception-navbar__link.is-disabled {
            color: rgba(255, 255, 255, 0.62);
            background: rgba(15, 23, 42, 0.10);
            cursor: not-allowed;
            user-select: none;
        }

        .reception-navbar__link.is-disabled:hover {
            color: rgba(255, 255, 255, 0.62);
            background: rgba(15, 23, 42, 0.10);
        }

        .reception-navbar__disabled-badge {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 2px 7px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.88);
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .reception-navbar__search {
            position: relative;
            width: min(340px, 100%);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.18);
        }

        .reception-navbar__search-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-size: 15px;
            z-index: 2;
        }

        .reception-navbar__search-input {
            height: 40px;
            padding-left: 38px;
            border: 0;
            border-radius: 12px;
            background: #ffffff;
            color: #1f2937;
            font-size: 14px;
            box-shadow: none;
        }

        .reception-navbar__search-input:focus {
            border: 0;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.28);
        }

        .reception-navbar__search-btn {
            height: 40px;
            padding: 0 15px;
            border: 0;
            border-radius: 12px;
            background: #ffffff;
            color: #036b78;
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
            transition: 0.18s ease;
        }

        .reception-navbar__search-btn:hover {
            background: #eef8fa;
            color: #024f59;
        }

        .reception-navbar__toggler {
            border: 0;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.18);
        }

        .reception-navbar__toggler:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.28);
        }

        .reception-inpatient-notice {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 24px;
            border-bottom: 1px solid #cfe8eb;
            background: #effbfc;
            color: #32606a;
            font-size: 12px;
            line-height: 1.5;
        }

        .reception-inpatient-notice > i {
            flex-shrink: 0;
            color: #078f9f;
        }

        .reception-inpatient-notice a {
            color: #047d8c;
            font-weight: 800;
            text-decoration: underline;
        }

        @media (max-width: 1199px) {
            .reception-navbar__inner {
                padding: 10px 16px;
            }

            .reception-navbar__brand {
                margin-right: 0;
            }

            .reception-navbar__mode {
                margin-left: 12px;
                margin-right: auto;
            }

            .reception-navbar .navbar-collapse {
                padding-top: 12px;
            }

            .reception-navbar__menu {
                align-items: stretch;
                gap: 8px;
                margin-bottom: 12px;
                margin-right: 0;
            }

            .reception-navbar__link {
                width: 100%;
            }

            .reception-navbar__search {
                width: 100%;
                margin-left: 0;
            }
        }

        @media (max-width: 576px) {
            .reception-inpatient-notice {
                padding: 10px 16px;
            }

            .reception-navbar__search {
                flex-direction: column;
                align-items: stretch;
            }

            .reception-navbar__search-icon {
                top: 16px;
            }

            .reception-navbar__search-btn {
                width: 100%;
            }
        }
    </style>
    <nav class="reception-navbar navbar navbar-expand-xl" aria-label="{{ $receptionNavigation->ariaLabel }}">
        <div class="container-fluid reception-navbar__inner">
            <a class="reception-navbar__brand"
               href="{{ route('doctors.reception.index') }}">
                <span class="reception-navbar__brand-icon">
                    <i class="bi {{ $receptionNavigation->brandIcon }}"></i>
                </span>
                <span>{{ $receptionNavigation->title }}</span>
            </a>

            @if($receptionNavigation->isInpatient)
                <span class="reception-navbar__mode"><i class="bi bi-building"></i> Стационар</span>
            @endif

            <button class="navbar-toggler reception-navbar__toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#receptionNavbar"
                    aria-controls="receptionNavbar"
                    aria-expanded="false"
                    aria-label="Открыть навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="receptionNavbar">
                <ul class="navbar-nav reception-navbar__menu">
                    @if($receptionNavigation->isInpatient)
                        <li class="nav-item">
                            <a class="reception-navbar__link {{ $receptionNavigation->isIndexRoute ? 'active' : '' }}"
                               href="{{ route('doctors.reception.index') }}"
                               @if($receptionNavigation->isIndexRoute) aria-current="page" @endif>
                                <i class="bi bi-clipboard2-pulse"></i>
                                Госпитализированные
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="reception-navbar__link {{ $receptionNavigation->isDischargesRoute ? 'active' : '' }}"
                               href="{{ route('doctors.inpatient.discharges.index') }}">
                                <i class="bi bi-file-earmark-medical"></i>
                                Выписки
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="reception-navbar__link {{ $receptionNavigation->isIndexRoute ? 'active' : '' }}"
                               href="{{ route('doctors.reception.index') }}"
                               @if($receptionNavigation->isIndexRoute) aria-current="page" @endif>
                                <i class="bi bi-list-check"></i>
                                Текущая регистратура
                            </a>
                        </li>
                        <li class="nav-item">
                            @if($receptionNavigation->quarantineActive)
                                <span class="reception-navbar__link is-disabled"
                                      aria-disabled="true"
                                      title="Создание записей отключено на период карантина до {{ $receptionNavigation->quarantineUntil }} включительно">
                                    <i class="bi bi-plus-circle"></i>
                                    Создать запись
                                    <span class="reception-navbar__disabled-badge">Отключено</span>
                                </span>
                            @else
                                <a class="reception-navbar__link {{ $receptionNavigation->isCreateRoute ? 'active' : '' }}"
                                   href="{{ route('doctors.reception.create') }}"
                                   @if($receptionNavigation->isCreateRoute) aria-current="page" @endif>
                                    <i class="bi bi-plus-circle"></i>
                                    Создать запись
                                </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            <a class="reception-navbar__link {{ $receptionNavigation->isArchivesRoute ? 'active' : '' }}"
                               href="{{ route('doctors.reception.archives') }}"
                               @if($receptionNavigation->isArchivesRoute) aria-current="page" @endif>
                                <i class="bi bi-archive"></i>
                                Архивы
                            </a>
                        </li>
                    @endif
                </ul>

                <form class="reception-navbar__search ms-xl-auto"
                      method="get"
                      action="{{ route('doctors.reception.index') }}"
                      role="search">
                    <i class="bi bi-search reception-navbar__search-icon"></i>
                    <input type="search"
                           name="{{ $receptionNavigation->searchField }}"
                           class="form-control reception-navbar__search-input"
                           aria-label="Поиск пациентов"
                           placeholder="{{ $receptionNavigation->searchPlaceholder }}"
                           value="{{ $receptionNavigation->searchValue }}">
                    <button type="submit" class="reception-navbar__search-btn">Найти</button>
                </form>
            </div>
        </div>
    </nav>

    @yield('sub-main')
@endsection
