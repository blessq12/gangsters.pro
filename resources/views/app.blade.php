<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Shell defaults; SPA перезапишет через usePageHead + resources/js/config/site.js --}}
    <title>Доставка суши и роллов в Томске | Gangster's Sushi</title>
    @include('inc.head-meta')
    @include('inc.verification')
    @include('inc.favicon')
    {{-- Метрика в head: _ym_status-check / отладка требуют ранней загрузки tag.js --}}
    @include('inc.yandex-metrika')
    @vite('resources/css/style.css')
    @vite('resources/js/app.js')
</head>

<body>
    <div id="app"></div>
</body>

</html>
