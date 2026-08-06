@extends('layouts.doctor')
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
            margin-right: 22px;
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
            gap: 6px;
        }

        .reception-navbar__link {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 14px;
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

        .reception-navbar__search {
            position: relative;
            width: min(420px, 100%);
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

        @media (max-width: 991px) {
            .reception-navbar__inner {
                padding: 10px 16px;
            }

            .reception-navbar__brand {
                margin-right: 0;
            }

            .reception-navbar .navbar-collapse {
                padding-top: 12px;
            }

            .reception-navbar__menu {
                align-items: stretch;
                gap: 8px;
                margin-bottom: 12px;
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
    <nav class="reception-navbar navbar navbar-expand-lg">
        <div class="container-fluid reception-navbar__inner">
            <a class="reception-navbar__brand" href="{{ route('doctors.reception.index') ?? '#' }}">
            <span class="reception-navbar__brand-icon">
                <i class="bi bi-hospital"></i>
            </span>
                <span>Регистратура</span>
            </a>

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
                    <li class="nav-item">
                        <a class="reception-navbar__link @if(is_route('doctors.reception.index')) active @endif"
                           href="{{ route('doctors.reception.index') ?? '#' }}">
                            <i class="bi bi-list-check"></i>
                            Текущая регистратура
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="reception-navbar__link @if(is_route('doctors.reception.create')) active @endif"
                           href="{{ route('doctors.reception.create') ?? '#' }}">
                            <i class="bi bi-plus-circle"></i>
                            Создать запись
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="reception-navbar__link @if(is_route('doctors.reception.archives')) active @endif"
                           href="{{ route('doctors.reception.archives') ?? '#' }}">
                            <i class="bi bi-archive"></i>
                            Архивы
                        </a>
                    </li>
                </ul>

                <form class="reception-navbar__search ms-lg-auto"
                      method="get"
                      action="{{ route('doctors.reception.index') ?? url()->current() }}">
                    <i class="bi bi-search reception-navbar__search-icon"></i>

                    <input type="text"
                           name="fullname"
                           class="form-control reception-navbar__search-input"
                           placeholder="Поиск в регистратуре..."
                           value="{{ request('fullname') }}">

                    <button type="submit" class="reception-navbar__search-btn">
                        Найти
                    </button>
                </form>
            </div>
        </div>
    </nav>
    @yield('sub-main')
@endsection
