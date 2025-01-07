<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Панель врача СМА Норхнейм</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite(['resources/sass/doctors.sass'])
    @yield('assets')
</head>
<body class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 280px; height: 100vh; position: sticky; top: 0;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <span class="fs-4">Навигация</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li><a href="{{ route('doctors.main') }}" class="nav-link link-body-emphasis"><i class="bi bi-speedometer2"></i> Статистика</a></li>
            <li><a href="#" class="nav-link link-body-emphasis"><i class="bi bi-person-lines-fill"></i> Регистратура</a></li>
            <li><a href="#" class="nav-link link-body-emphasis"><i class="bi bi-ui-checks-grid"></i> Тесты</a></li>
            <li><a href="#" class="nav-link link-body-emphasis"><i class="bi bi-search-heart"></i> Анализы</a></li>
            <li><a href="{{ route('doctors.prescriptions') }}" class="nav-link link-body-emphasis"><i class="bi bi-prescription"></i> Выписки</a></li>
            <li><a href="#" class="nav-link link-body-emphasis"><i class="bi bi-gear"></i> Настройки</a></li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://github.com/mdo.png" alt="Avatar" width="32" height="32" class="rounded-circle me-2">
                <strong>{{ auth()->user()->login }}</strong>
            </a>
            <ul class="dropdown-menu text-small shadow">
                <li><a class="dropdown-item" href="{{ route('main.index') }}">Главная</a></li>
                <li><a class="dropdown-item" href="#">Настройки</a></li>
                <li><a class="dropdown-item" href="#">Профиль</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('actions.logout') }}">Выход</a></li>
            </ul>
        </div>
    </div>

    <!-- Main content -->
    <div class="main-content flex-grow-1">
        @yield('main')
    </div>
</body>
</html>
