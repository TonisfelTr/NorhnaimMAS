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
                    <li class="nav-item">
                        <a class="settings-navbar__link {{ is_route('doctors.settings.index') ? 'active' : '' }}"
                           href="{{ route('doctors.settings.index') }}">
                            <i class="bi bi-sliders"></i>
                            Общие
                        </a>
                    </li>

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

                    <li class="nav-item dropdown settings-navbar__dropdown">
                        <button type="button"
                                class="settings-navbar__link settings-navbar__dropdown-toggle dropdown-toggle {{ (is_route('doctors.settings.clinic-schedule') || is_route('doctors.settings.vacation-schedule')) ? 'active' : '' }}"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="bi bi-calendar-week"></i>
                            Графики
                        </button>

                        <div class="dropdown-menu settings-navbar__dropdown-menu">
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

                    <li class="nav-item">
                        <a class="settings-navbar__link {{ is_route('doctors.settings.doctors-schedule') ? 'active' : '' }}"
                           href="{{ route('doctors.settings.doctors-schedule') }}">
                            <i class="bi bi-calendar2-range"></i>
                            Расписание врачей
                        </a>
                    </li>
                </ul>

                @if(auth()->user()?->doctor)
                    <div class="settings-navbar__context">
                        <i class="bi {{ auth()->user()->doctor->clinic_id ? 'bi-hospital' : 'bi-person-badge' }}"></i>
                        {{ auth()->user()->doctor->clinic_id ? 'Клиника' : 'Частная практика' }}
                    </div>
                @endif
            </div>
        </div>
    </nav>
