@php
    $yandexCounters = config('services.yandex.counters', []);
@endphp
@if (is_array($yandexCounters) && $yandexCounters !== [])
    <script>
        window.dataLayer = window.dataLayer || [];
    </script>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

        @foreach ($yandexCounters as $counterId)
            ym({{ (int) $counterId }}, 'init', {
                webvisor: true,
                clickmap: true,
                ecommerce: 'dataLayer',
                referrer: document.referrer,
                url: location.href,
                accurateTrackBounce: true,
                trackLinks: true
            });
        @endforeach
    </script>
    @foreach ($yandexCounters as $counterId)
        <noscript>
            <div><img src="https://mc.yandex.ru/watch/{{ (int) $counterId }}" style="position:absolute; left:-9999px;" alt="" /></div>
        </noscript>
    @endforeach
    <!-- /Yandex.Metrika counter -->
@endif
