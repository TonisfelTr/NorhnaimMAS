<!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="no-referrer"><meta name="robots" content="noindex,nofollow">
<title>Подключение недоступно</title><link rel="stylesheet" href="{{ asset('css/clinic-telemedicine-session.css') }}"></head>
<body class="ctm-public"><main class="ctm-session ctm-session--public">
<h1>Подключение недоступно</h1><section class="ctm-card">
@foreach($messages as $message)<p>{{ $message }}</p>@endforeach
<p class="ctm-muted">Свяжитесь с клиникой, чтобы уточнить время или получить новое приглашение.</p>
</section></main></body></html>
