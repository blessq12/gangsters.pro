<?php

use App\Support\Site\SiteSeoResolver;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $base = rtrim((string) config('site.canonical_base'), '/');
    $paths = app(SiteSeoResolver::class)->indexablePaths();
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
    $manifestIconDefs = (array) config('site.manifest_icons', []);
    $icon192 = (string) config('site.icon_192');

    foreach ($manifestIconDefs as $iconDef) {
        if (($iconDef['sizes'] ?? '') === '192x192') {
            $icon192 = (string) ($iconDef['path'] ?? $icon192);
            break;
        }
    }

    $icons = array_map(static function (array $iconDef): array {
        $entry = [
            'src' => (string) ($iconDef['path'] ?? ''),
            'sizes' => (string) ($iconDef['sizes'] ?? ''),
            'type' => (string) ($iconDef['type'] ?? 'image/png'),
        ];
        if (! empty($iconDef['purpose'])) {
            $entry['purpose'] = (string) $iconDef['purpose'];
        }

        return $entry;
    }, $manifestIconDefs);

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

Route::get('/images/og/{asset}', function (string $asset) {
    $safeName = basename($asset);
    if ($safeName !== $asset) {
        abort(404);
    }

    $path = public_path('images/og/'.$safeName);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('asset', '[a-zA-Z0-9\-\.]+');

Route::get('/favicon/{asset}', function (string $asset) {
    $safeName = basename($asset);
    if ($safeName !== $asset) {
        abort(404);
    }

    $path = public_path('favicon/'.$safeName);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('asset', '[a-zA-Z0-9\-\.]+');

Route::view('/{any?}', 'app')->where('any', '.*');
