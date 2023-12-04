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
    <x-crm.navbar></x-crm.navbar>
    <div class="container-fluid">
        <div class="row">

            <div class="col-12 col-lg-3">
                <x-crm.sidebar></x-crm.sidebar>
            </div>
            <div class="col-12 col-lg-9 py-4">
                <div class="row">
                    <div class="col-12">
                        <h3>
                            @yield('title')
                        </h3>
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
</body>
</html>