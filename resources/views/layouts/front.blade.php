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
    <div id="app">    
        <app-init 
            :links='@json($links)'
            :company='@json(\App\Models\Company::first())'
        ></app-init>
        <x-front.navbar
            :links="$links"
        ></x-front.navbar>
        @hasSection('hero')
            @yield('hero')
        @endif
        @yield('content')
        <x-front.footer></x-front.footer>
    </div>
</body>
</html>