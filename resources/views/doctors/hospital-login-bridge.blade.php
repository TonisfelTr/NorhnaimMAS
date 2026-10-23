<!doctype html>
<html lang="ru">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Настройки стационара</title></head>
<body>
    <p style="font-family:system-ui,sans-serif;padding:24px">Открываем настройки выбранного стационара…</p>
    <script>
        // Ключ живёт только в текущей вкладке; при закрытии вкладки он исчезает.
        try { sessionStorage.setItem('norhnaim:hospital-login-tab', @json($token)); } catch (_) {}
        window.location.replace(@json($destination));
    </script>
</body>
</html>
