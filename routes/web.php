<?php

use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $base = rtrim((string) config('site.canonical_base'), '/');
    $paths = ['/', '/about', '/delivery', '/contacts'];
    $entries = array_map(static function (string $path) use ($base): string {
        $loc = $path === '/' ? $base.'/' : $base.$path;
        $priority = $path === '/' ? '1.0' : '0.8';
        $changefreq = $path === '/' ? 'weekly' : 'monthly';

        return '  <url>'
            .'<loc>'.e($loc).'</loc>'
            .'<changefreq>'.$changefreq.'</changefreq>'
            .'<priority>'.$priority.'</priority>'
            .'</url>';
    }, $paths);

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        .implode('', $entries)
        .'</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
});

Route::get('/favicon/site.webmanifest', function () {
    $icon192 = (string) config('site.icon_192');
    $icon512 = (string) config('site.icon_512');

    $icons = [
        [
            'src' => $icon192,
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $icon512,
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $icon512,
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ];

    $shortcuts = array_map(static function (array $shortcut) use ($icon192): array {
        return [
            'name' => $shortcut['name'],
            'short_name' => $shortcut['short_name'],
            'url' => $shortcut['url'],
            'icons' => [
                [
                    'src' => $shortcut['icon'] ?? $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
            ],
        ];
    }, (array) config('site.manifest_shortcuts', []));

    $manifest = [
        'id' => (string) config('site.manifest_id'),
        'name' => (string) config('site.name'),
        'short_name' => (string) config('site.short_name'),
        'description' => (string) config('site.default_description'),
        'start_url' => (string) config('site.start_url'),
        'scope' => (string) config('site.scope'),
        'lang' => (string) config('site.lang'),
        'display' => (string) config('site.display'),
        'theme_color' => (string) config('site.theme_color'),
        'background_color' => (string) config('site.background_color'),
        'icons' => $icons,
        'shortcuts' => $shortcuts,
    ];

    return response()->json($manifest, 200, [
        'Content-Type' => 'application/manifest+json; charset=UTF-8',
    ]);
});

Route::view('/{any?}', 'app')->where('any', '.*');
