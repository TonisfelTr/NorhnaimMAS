<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Подтверждение привязки медицинской карты</title>
</head>
<body style="margin:0;padding:32px;background:#f3f6f4;font-family:Arial,sans-serif;color:#19312f">
    <div style="max-width:560px;margin:0 auto;padding:30px;background:#fff;border:1px solid #dce6e2;border-radius:16px">
        <div style="font-size:11px;font-weight:700;letter-spacing:2px;color:#1f746d;text-transform:uppercase">
            Норхнейм
        </div>

        <h1 style="margin:14px 0 10px;font-size:22px">
            Подтверждение личности
        </h1>

        <p style="font-size:14px;line-height:1.6;color:#60716d">
            Для подтверждения привязки личного кабинета к медицинской карте
            введите код на странице Норхнейма.
        </p>

        <div style="margin:26px 0;padding:18px;border-radius:12px;background:#edf6f2;text-align:center">
            <strong style="font-size:32px;letter-spacing:8px;color:#1f746d">
                {{ $code }}
            </strong>
        </div>

        <p style="font-size:12px;line-height:1.6;color:#758582">
            Код действует 15 минут. Если вы не запрашивали привязку медицинской карты,
            просто проигнорируйте это письмо.
        </p>

        <p style="margin-top:28px;font-size:11px;color:#9aa7a3">
            Это автоматическое сообщение. Не отвечайте на него.
        </p>
    </div>
</body>
</html>
