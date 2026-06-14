<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Подтверждение врача</title>
    <style>
        body{margin:0;background:#f6f7fb;font-family:system-ui,Segoe UI,Roboto,sans-serif}
        .wrap{max-width:520px;margin:8vh auto;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px}
        h1{font-size:20px;margin:0 0 6px}
        .muted{color:#6b7280;font-size:14px}
        input{width:95%;padding:14px 12px;border:1px solid #e5e7eb;border-radius:12px;font-size:22px;letter-spacing:2px;text-align:center}
        button{width:100%;padding:12px 16px;border-radius:12px;border:0;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
        button[disabled]{opacity:.6}
        .err{margin-top:10px;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;padding:10px 12px;border-radius:10px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Подтверждение врача</h1>
    <div class="muted" style="margin-bottom:14px">
        Пациент завершил тест. Чтобы открыть ответы и снять блокировку панели, введите PIN.
    </div>

    <form method="POST" action="{{ route('doctors.tests.final_pin.verify', $session) }}">
        @csrf

        <input
            name="pin"
            inputmode="numeric"
            maxlength="6"
            placeholder="••••••"
            autocomplete="one-time-code"
            value="{{ old('pin') }}"
            autofocus
        >

        <button type="submit" style="margin-top:12px">Подтвердить</button>

        @if($errors->has('pin'))
            <div class="err">{{ $errors->first('pin') }}</div>
        @endif
    </form>
</div>
</body>
</html>
