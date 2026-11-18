<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Бэкофис | Gangster's Sushi</title>
    <script>
        window.__BACKOFFICE__ = @json([
            'authRequired' => (bool) config('backoffice.auth_required'),
        ]);
    </script>
    @vite(['apps/backoffice/app.js'], 'build/backoffice')
</head>

<body>
    <div id="app"></div>
</body>

</html>
