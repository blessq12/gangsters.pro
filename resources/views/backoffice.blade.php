<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Backoffice | Gangster's Sushi</title>
    <script>
        window.__BACKOFFICE__ = @json([
            'authRequired' => (bool) config('backoffice.auth_required'),
        ]);
    </script>
    @vite(['resources/css/style.css', 'resources/js/backoffice/app.js'])
</head>

<body>
    <div id="app"></div>
</body>

</html>
