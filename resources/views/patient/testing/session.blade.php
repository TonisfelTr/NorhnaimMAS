<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тестирование</title>
    <style>
        body{margin:0;background:#f6f7fb;font-family:system-ui,Segoe UI,Roboto,sans-serif}
        .wrap{max-width:520px;margin:8vh auto;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px}
        h1{font-size:20px;margin:0 0 6px}
        .muted{color:#6b7280;font-size:14px}
        input{width:95%;padding:14px 12px;border:1px solid #e5e7eb;border-radius:12px;font-size:22px;letter-spacing:2px;text-align:center}
        button{width:100%;padding:12px 16px;border-radius:12px;border:0;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
        button[disabled]{opacity:.6}
        .err{display:none;margin-top:10px;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;padding:10px 12px;border-radius:10px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Начало тестирования</h1>
    <div class="muted" style="margin-bottom:14px">Введите PIN, который сообщил сотрудник</div>
    <input id="pin" inputmode="numeric" maxlength="6" placeholder="••••••" autocomplete="one-time-code">
    <button id="btn" style="margin-top:12px">Начать</button>
    <div id="err" class="err"></div>
</div>

<script>
    (function(){
        const token = @json($token);
        const csrf  = document.querySelector('meta[name="csrf-token"]').content;
        const err   = document.getElementById('err');
        const btn   = document.getElementById('btn');
        const pin   = document.getElementById('pin');

        function showErr(t){ err.textContent=t; err.style.display='block'; }
        function hideErr(){ err.style.display='none'; }

        btn.addEventListener('click', async () => {
            hideErr();
            const val = (pin.value||'').trim();
            if (val.length < 4) return showErr('Введите корректный PIN');

            btn.disabled = true;
            try {
                const url = `{{ route('doctors.patients.ts.unlock','__TOK__') }}`.replace('__TOK__', token);
                const r = await fetch(url, {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({ token: token, pin: val })
                });
                if (!r.ok) throw new Error('HTTP '+r.status);
                const data = await r.json();
                const target =
                    data.run_url ??
                    (data.session_id
                        ? `{{ route('doctors.reception.tests.run', '__SID__') }}`.replace('__SID__', data.session_id)
                        : null);
                if (!target) throw new Error('Не удалось определить URL теста');
                location.href = target;
            } catch(e) {
                showErr('Неверный или просроченный PIN. Обратитесь к сотруднику.');
            } finally {
                btn.disabled = false;
            }
        });
    })();
</script>
</body>
</html>
