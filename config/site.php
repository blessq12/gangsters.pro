<?php

$canonicalBase = rtrim((string) env('CLIENT_FRONTEND_URL', env('APP_URL', 'http://localhost')), '/');
$faviconBase = '/favicon';

$pwaDisplayName = env('SITE_PWA_DISPLAY_NAME', 'Гангстерс Суши');

return [
    'name' => env('SITE_NAME', $pwaDisplayName),
    'short_name' => env('SITE_SHORT_NAME', $pwaDisplayName),
    'default_title' => env(
        'SITE_DEFAULT_TITLE',
        "Доставка суши и роллов в Томске | Gangster's Sushi",
    ),
    'default_description' => env(
        'SITE_DEFAULT_DESCRIPTION',
        'Закажи суши, роллы и горячие блюда с доставкой по Томску. Gangster\'s Sushi — быстрая доставка и актуальное меню онлайн.',
    ),
    'canonical_base' => $canonicalBase,
    /** Совпадает с --app-canvas в MainLayout / style.css */
    'theme_color' => '#191919',
    'background_color' => '#191919',
    'og_locale' => 'ru_RU',
    'og_type' => 'website',
    'og_image_path' => $faviconBase.'/web-app-manifest-512x512.png',
    'twitter_card' => 'summary_large_image',
    'apple_mobile_web_app_title' => env('SITE_PWA_DISPLAY_NAME', $pwaDisplayName),

    'favicon_base' => $faviconBase,
    'favicon_svg' => $faviconBase.'/favicon.svg',
    'favicon_ico' => $faviconBase.'/favicon.ico',
    'favicon_png_96' => $faviconBase.'/favicon-96x96.png',
    'apple_touch_icon' => $faviconBase.'/apple-touch-icon.png',
    'icon_192' => $faviconBase.'/web-app-manifest-192x192.png',
    'icon_512' => $faviconBase.'/web-app-manifest-512x512.png',
    'manifest_icons' => [
        [
            'path' => $faviconBase.'/web-app-manifest-192x192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'path' => $faviconBase.'/web-app-manifest-512x512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'path' => $faviconBase.'/web-app-manifest-512x512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],
    'browserconfig_path' => $faviconBase.'/browserconfig.xml',

    'manifest_path' => $faviconBase.'/site.webmanifest',
    'manifest_id' => '/',
    'start_url' => '/?utm_source=pwa',
    'scope' => '/',
    'display' => 'standalone',
    'lang' => 'ru',

    'manifest_shortcuts' => [
        [
            'name' => 'Меню',
            'short_name' => 'Меню',
            'url' => '/',
            'icon' => $faviconBase.'/web-app-manifest-192x192.png',
        ],
        [
            'name' => 'Доставка',
            'short_name' => 'Доставка',
            'url' => '/delivery',
            'icon' => $faviconBase.'/web-app-manifest-192x192.png',
        ],
    ],
];
