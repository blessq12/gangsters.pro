<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="@yield('desc')">
    <title>@yield('title')</title>
    @include('inc/favicon')
    @include('inc/cdn')
    @vite('resources/sass/front/app.sass')
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
        <x-front.footer />
    </div>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m, e, t, r, i, k, a) {
            m[i] = m[i] || function() {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(
                k, a)
        })
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(57393940, "init", {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            ecommerce: "dataLayer"
        });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/57393940" style="position:absolute; left:-9999px;" alt="" />
        </div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
</body>

</html>
