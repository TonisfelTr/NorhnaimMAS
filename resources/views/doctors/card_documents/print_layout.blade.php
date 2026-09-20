<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('print_title', 'Медицинский документ')</title>
    <link rel="stylesheet" href="{{ asset('css/medical-card-documents.css') }}?v=20260910-1">
    <script src="{{ asset('js/medical-card-documents.js') }}?v=20260910-1" defer></script>
</head>
<body class="mcd-print-page" data-mcd-autoprint>
    <div class="mcd-print-toolbar">
        <button type="button" class="mcd-button" data-mcd-print>Распечатать</button>
        <span>Для PDF выберите «Сохранить в PDF» в диалоге печати браузера.</span>
    </div>
    @yield('print_body')
</body>
</html>
