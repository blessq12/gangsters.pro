<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Заголовок не назначен')</title>
    @include('inc.cdn')
    @vite('resources/sass/crm/app.sass')
</head>
<body>
    @yield('content')
</body>
</html>