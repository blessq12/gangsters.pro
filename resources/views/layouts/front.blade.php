<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Название страницы не задано')</title>
    <meta name="description" content="@yield('desc', 'Описание страницы не заддано')">
    @include('inc.cdn')
    @vite('resources/js/app.js')
    @vite('resources/sass/front/app.sass')
</head>
<body>
    <div id="app">
        @if (Request::segment(1) !== null)
            <x-front.navbar></x-front.navbar>
        @endif
        @yield('content')
    </div>
</body>
</html>