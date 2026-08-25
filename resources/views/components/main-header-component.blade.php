<nav class="navbar navbar-expand-lg nh-navbar" aria-label="Основная навигация">
    <div class="container-xl nh-navbar__container">
        <a class="navbar-brand nh-navbar__brand" href="{{ route('main.index') }}" aria-label="Норхнейм — на главную">
            <img
                src="{{ asset('assets/images/home/norhneim-viking-mark.png') }}"
                alt=""
                aria-hidden="true"
                class="nh-navbar__brand-mark"
            >
            <span class="nh-navbar__brand-copy">
                <strong>Норхнейм</strong>
                <small>клиническая система</small>
            </span>
        </a>

        <button
            class="navbar-toggler nh-navbar__toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNavAltMarkup"
            aria-controls="navbarNavAltMarkup"
            aria-expanded="false"
            aria-label="Открыть меню"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse nh-navbar__collapse" id="navbarNavAltMarkup">
            <ul class="navbar-nav nh-navbar__nav ms-lg-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ is_route('main.index') ? 'active' : '' }}" href="{{ route('main.index') }}">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ is_route('main.clinics') ? 'active' : '' }}" href="{{ route('main.clinics') }}">Врачи и клиники</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ is_route('main.medicines') ? 'active' : '' }}" href="{{ route('main.medicines') }}">Реестр лекарств</a>
                </li>

                <li class="nav-item dropdown">
                    <a
                        class="nav-link dropdown-toggle"
                        href="#"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Статьи
                    </a>
                    <ul class="dropdown-menu nh-navbar__dropdown">
                        <li><a class="dropdown-item" href="{{ route('main.blog') }}">Блог</a></li>
                        <li><a class="dropdown-item" href="{{ route('main.jurisprudence') }}">Юриспруденция</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ is_route('main.feedback') ? 'active' : '' }}" href="{{ route('main.feedback') }}">Обратная связь</a>
                </li>

                @if(!is_authed())
                    <li class="nav-item nh-navbar__auth-item">
                        <a class="nav-link nh-navbar__auth-link" href="#" data-bs-toggle="modal" data-bs-target="#authorization-block">
                            Авторизация
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown nh-navbar__profile">
                        <a
                            class="nav-link dropdown-toggle nh-navbar__profile-toggle"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <span class="nh-navbar__profile-icon"><i class="bi bi-person-fill"></i></span>
                            <span class="nh-navbar__profile-name">{{ auth()->user()->login }}</span>
                            <span class="nh-navbar__balance">{{ auth()->user()->balance }} ₽</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end nh-navbar__dropdown">
                            @can('adminpanel_see')
                                <li>
                                    <a class="dropdown-item" href="{{ route('filament.admin.pages.dashboard') }}">
                                        <i class="bi bi-gear"></i>
                                        <span>Панель управления</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endcan
                            <li>
                                <a class="dropdown-item" href="{{ route('doctors.main') }}">
                                    <i class="bi bi-hospital"></i>
                                    <span>Панель доктора</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-file-medical"></i>
                                    <span>Панель пациента</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('actions.logout') }}">
                                    <i class="bi bi-box-arrow-left"></i>
                                    <span>Выход</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
