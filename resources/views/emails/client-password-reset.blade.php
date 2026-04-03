<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Восстановление пароля</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1f1f23;">
    <p>Запрос на сброс пароля личного кабинета.</p>
    <p>Перейди по ссылке (действует ограниченное время):</p>
    <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
    <p style="font-size: 12px; color: #64748b;">Если это не ты — просто проигнорируй письмо.</p>
</body>
</html>
