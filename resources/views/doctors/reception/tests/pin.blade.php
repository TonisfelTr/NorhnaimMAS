<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Подтверждение PIN</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body{font-family:system-ui,sans-serif;background:#f5f7fb;margin:0}
        .wrap{max-width:420px;margin:8vh auto;background:#fff;border-radius:12px;padding:28px;
            box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .h{margin:0 0 8px}
        .muted{color:#667085;margin:0 0 18px}
        input[type="text"]{width:100%;font-size:20px;letter-spacing:4px;padding:12px 14px;border:1px solid #d0d5dd;border-radius:10px}
        button{margin-top:14px;width:100%;padding:12px 16px;border:0;border-radius:10px;font-weight:600;background:#1f6bff;color:#fff;cursor:pointer}
        .err{color:#b42318;margin-top:10px}
    </style>
</head>
<body>
<div class="wrap">
    <h2 class="h">Введите PIN</h2>
    <p class="muted">Для продолжения теста введите шестизначный PIN.</p>

    <form method="post" action="{{ route('doctors.tests.pin.verify', $assignment) }}">
        @csrf
        <input type="text" inputmode="numeric" maxlength="6" name="pin" autofocus>
        <button type="submit">Подтвердить</button>
        @error('pin') <div class="err">{{ $message }}</div> @enderror
        @if(session('error')) <div class="err">{{ session('error') }}</div> @endif
    </form>
</div>
</body>
</html>
