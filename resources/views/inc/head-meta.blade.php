@php
    $siteName = (string) config('site.name');
    $title = (string) ($pageSeo['title'] ?? config('site.default_title'));
    $description = (string) ($pageSeo['description'] ?? config('site.default_description'));
    $robots = (string) ($pageSeo['robots'] ?? 'index,follow');
    $canonicalBase = rtrim((string) config('site.canonical_base'), '/');
    $path = request()->path();
    $canonicalUrl = $canonicalBase . ($path === '' || $path === '/' ? '' : '/' . ltrim($path, '/'));
    $ogImagePath = (string) config('site.og_image_social_path');
    $ogImageUrl = $canonicalBase . $ogImagePath;
@endphp
<meta name="description" content="{{ $description }}">
<meta name="color-scheme" content="dark">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:locale" content="{{ config('site.og_locale') }}">
<meta property="og:type" content="{{ config('site.og_type') }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta name="twitter:card" content="{{ config('site.twitter_card') }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ config('site.apple_mobile_web_app_title') }}">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
