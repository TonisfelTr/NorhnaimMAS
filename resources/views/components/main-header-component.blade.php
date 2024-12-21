<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-inline-flex justify-content-center w-100">
                <li class="nav-item">
                    <a class="nav-link @if(is_route('main.index')) active" aria-current="page" @else " @endif href="{{ route('main.index') }}">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(is_route('main.clinics')) active" aria-current="page" @else " @endif href="{{ route('main.clinics') }}">Врачи и клиники</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(is_route('main.medicines')) active" aria-current="page" @else " @endif href="{{ route('main.medicines') }}">Реестр лекарств</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Статьи
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('main.blog') }}">Блог</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('main.jurisprudence') }}">Юриспруденция</a></li>
                        <li><a class="dropdown-item" href="{{ route('main.articles') }}">Научная библиотека</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(is_route('main.feedback')) active" aria-current="page" @else " @endif href="{{ route('main.feedback') }}">Обратная связь</a>
                </li>
                @if(!is_authed())
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#authorization-block">Авторизация</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->login }}     <span class="badge text-bg-primary">{{ auth()->user()->formattedBalance() }} ₽</span>
                        </a>
                        <ul class="dropdown-menu">
                            @permission('adminpanel_see')
                                <li><a class="dropdown-item" href="{{ route('admin.main') }}">
                                        <i class="bi bi-gear"></i> Панель управления</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endpermission
                            <li><a class="dropdown-item" href="#"><i class="bi bi-hospital"></i> Панель доктора</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-file-medical"></i> Панель пациента</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('actions.logout') }}"><i class="bi bi-box-arrow-left"></i> Выход</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
