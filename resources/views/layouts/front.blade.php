<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="@yield('desc')">
    <title>@yield('title')</title>
    @include('inc/cdn')
    @include('inc/favicon')
    @vite('resources/sass/front/app.sass')
    @vite('resources/js/app.js')
    @vite('resources/js/front/app.js')
</head>
<body>
    @if (Admin::user())
        <x-front.admin-bar></x-front.admin-bar>
    @endif
    <div id="app">    
        <app-init></app-init>
        <x-front.navbar></x-front.navbar>
        @yield('content')
        <x-front.footer></x-front.footer>
    </div>
</body>
</html>