<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Прохождение теста')</title>

    {{-- Vite --}}
    @vite(['resources/js/app.js'])
    @vite(['resources/js/test-run.js'])

    {{-- Если у тебя Laravel Mix вместо Vite — замени на:
         <script src="{{ mix('/js/test-run.js') }}" defer></script>
    --}}
</head>
<body style="margin:0;background:#f5f7fb;font-family:system-ui,Segoe UI,Roboto,sans-serif">
@yield('content')
</body>
</html>
