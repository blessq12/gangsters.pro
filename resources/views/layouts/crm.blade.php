<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Заголовок не назначен')</title>
    @include('inc.cdn')
    @vite('resources/sass/crm/app.sass')
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <x-crm.navbar></x-crm.navbar>
        <div class="container-fluid">
            <div class="row">

                <div class="col-12 col-lg-2">
                    <x-crm.sidebar></x-crm.sidebar>
                </div>
                <div class="col-12 col-lg-10 py-4 overflow-auto" style="height: calc(100vh - 70px)">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-4">
                                @yield('title')
                            </h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>