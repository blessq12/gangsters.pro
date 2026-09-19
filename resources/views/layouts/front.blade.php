<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="@yield('desc')">
    <meta name="yandex-verification" content="636b80119679b3bc" />
    <meta name="google-site-verification" content="05wqFmUG5VbvFUFGjYi-dXDSL_a0ts7FVzr5v0edg8Y" />
    <title>@yield('title')</title>
    @include('inc/favicon')
    @include('inc/cdn')
    @vite('resources/sass/style.css')
    @vite('resources/js/app.js')
</head>

<body>
    <div id="app">
        <app-init></app-init>
        <x-front.navbar />
        @hasSection('hero')
            @yield('hero')
        @endif
        @yield('content')
        <holiday-schedule></holiday-schedule>
        <x-front.footer />
    </div>
</body>

</html>
