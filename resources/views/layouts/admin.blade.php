<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Администраторская панель СМА Норхнейм</title>
    @vite('resources/sass/admin.sass')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHAV3_SITEKEY') }}"></script>
    @vite('resources/js/admin_app.js')
    @yield('assets')
    <style>
        /* Делаем сайдбар фиксированным */
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto; /* Прокрутка для длинного контента */
        }
        /* Основной контент прокручивается отдельно */
        .main-body {
            overflow-y: auto;
            height: 100vh;
        }

        td .btn-light {
            background: white;
            border: none;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar text-bg-dark d-flex flex-column p-3">
        <h4 class="pt-2">Навигация</h4>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item pb-2">
                <a href="{{ route('admin.main') }}" class="nav-link navbar-header text-white">
                    <i class="bi bi-bar-chart-steps"></i> Главная
                </a>
            </li>
            <li class="nav-item pb-2">
                <a href="{{ route('admin.settings') }}" class="nav-link navbar-header text-white">
                    <i class="bi bi-gear-fill"></i> Настройки
                </a>
            </li>
            <li class="nav-item pb-2">
                <a href="{{ route('admin.dictionary.registration') }}" class="nav-link navbar-header text-white">
                    <i class="bi bi-archive-fill"></i> Регистратура
                </a>
            </li>
            <li class="nav-item pb-2">
                <a href="#accounts" class="nav-link text-white toggle-collapse navbar-header" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="accounts" id="accountsToggle">
                    <i class="bi bi-file-earmark-text"></i> Учётные записи
                </a>
                <div class="collapse" id="accounts">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.users') }}" class="nav-link text-white">
                                <i class="bi bi-person-fill"></i> Пользователи
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.groups') }}" class="nav-link text-white">
                                <i class="bi bi-people-fill"></i> Группы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users.banned') }}" class="nav-link text-white">
                                <i class="bi bi-person-fill-slash"></i> Заблокированные
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item pb-2">
                <a href="#libraries" class="nav-link text-white toggle-collapse navbar-header" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="libraries" id="librariesToggle">
                    <i class="bi bi-collection-fill"></i> Библиотеки
                </a>
                <div class="collapse" id="libraries">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.patients') }}" class="nav-link text-white">
                                <i class="bi bi-file-person-fill"></i> Пациенты
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users.doctors') }}" class="nav-link text-white">
                                <i class="bi bi-person-vcard"></i> Доктора
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.dictionary.drugs') }}" class="nav-link text-white">
                                <i class="bi bi-capsule"></i> Лекарства
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.dictionary.diagnoses') }}" class="nav-link text-white">
                                <i class="bi bi-lungs"></i> Диагнозы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.dictionary.clinics') }}" class="nav-link text-white">
                                <i class="bi bi-hospital"></i> Клиники
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.jurisprudence.lawyers') }}" class="nav-link text-white">
                                <i class="bi bi-briefcase"></i> Юристы
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item pb-2">
                <a href="#blog" class="nav-link text-white toggle-collapse navbar-header" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="libraries" id="blogToggle">
                    <i class="bi bi-chat-left-dots"></i> Блог
                </a>
                <div class="collapse" id="blog">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.blog.categories') }}" class="nav-link text-white">
                                <i class="bi bi-bookmark"></i> Категории
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.blog.topics') }}" class="nav-link text-white">
                                <i class="bi bi-blockquote-left"></i> Статьи
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item pb-2">
                <a href="{{ route('admin.feedbacks') }}" class="nav-link text-white navbar-header">
                    <i class="bi bi-chat-right-text"></i> Обратная связь
                </a>
            </li>
        </ul>

        <!-- Аутентификация внизу -->
        <hr class="mt-auto">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link navbar-header text-white" data-bs-toggle="collapse" href="#profileCollapse" role="button" aria-expanded="false" aria-controls="profileCollapse">
                    {{ auth()->user()->login }}
                </a>
                <div class="collapse" id="profileCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('main.index') }}" class="nav-link text-white">На сайт</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('actions.logout') }}" class="nav-link text-white">Выход</a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main content area -->
    <div class="flex-grow-1 p-4 bg-light main-body">
        @yield('main')
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = ['accounts', 'libraries', 'profileCollapse'];
        toggles.forEach(id => {
            const isExpanded = localStorage.getItem(id);
            const element = document.getElementById(id);
            const toggle = document.getElementById(`${id}Toggle`);
            if (isExpanded === 'true' && element) {
                element.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
        toggles.forEach(id => {
            const toggle = document.getElementById(`${id}Toggle`);
            if (toggle) {
                toggle.addEventListener('click', function () {
                    const isExpanded = document.getElementById(id).classList.contains('show');
                    localStorage.setItem(id, !isExpanded);
                });
            }
        });
    });
</script>
</body>
</html>
