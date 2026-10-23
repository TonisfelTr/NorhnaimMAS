<nav class="settings-navbar navbar navbar-expand-xl" aria-label="Настройки стационара">
    <div class="container-fluid settings-navbar__inner">
        <a class="settings-navbar__brand" href="{{ route('doctors.settings.index') }}">
            <span class="settings-navbar__brand-icon">
                <i class="bi bi-gear"></i>
            </span>
            <span>Настройки</span>
        </a>

        <button class="navbar-toggler settings-navbar__toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#settingsNavbarInpatient"
                aria-controls="settingsNavbarInpatient"
                aria-expanded="false"
                aria-label="Открыть навигацию настроек стационара">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="settingsNavbarInpatient">
            <ul class="navbar-nav settings-navbar__menu">
                <li class="nav-item">
                    <a class="settings-navbar__link {{ is_route('doctors.settings.index') ? 'active' : '' }}"
                       href="{{ route('doctors.settings.index') }}">
                        <i class="bi bi-sliders"></i>
                        Общие
                    </a>
                </li>

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

                <li class="nav-item dropdown settings-navbar__dropdown">
                    <button type="button"
                            class="settings-navbar__link settings-navbar__dropdown-toggle dropdown-toggle {{ is_route('doctors.settings.vacation-schedule') ? 'active' : '' }}"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="bi bi-calendar-week"></i>
                        Графики
                    </button>

                    <div class="dropdown-menu settings-navbar__dropdown-menu">
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
            </ul>

            <div class="settings-navbar__context">
                <i class="bi bi-hospital"></i>
                Стационар
            </div>
        </div>
    </div>
</nav>
