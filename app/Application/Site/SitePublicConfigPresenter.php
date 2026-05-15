<?php

namespace App\Application\Site;

final class SitePublicConfigPresenter
{
    /**
     * Публичный контракт для window.__SITE__ (camelCase).
     *
     * @return array<string, mixed>
     */
    public function forClient(): array
    {
        $pwaDisplayName = (string) config('site.apple_mobile_web_app_title');

        $mapsApiKey = config('services.yandex_maps.api_key');

        return [
            'name' => (string) config('site.name'),
            'yandexMapsApiKey' => is_string($mapsApiKey) && $mapsApiKey !== '' ? $mapsApiKey : null,
            'shortName' => (string) config('site.short_name'),
            'pwaDisplayName' => $pwaDisplayName,
            'defaultTitle' => (string) config('site.default_title'),
            'defaultDescription' => (string) config('site.default_description'),
            'themeColor' => (string) config('site.theme_color'),
            'backgroundColor' => (string) config('site.background_color'),
            'ogLocale' => (string) config('site.og_locale'),
            'ogType' => (string) config('site.og_type'),
            'ogImagePath' => (string) config('site.og_image_path'),
            'ogImageSocialPath' => (string) config('site.og_image_social_path'),
            'twitterCard' => (string) config('site.twitter_card'),
            'defaultRobots' => 'index,follow',
            'pwaInstallDismissKey' => 'gangsters_pwa_install_dismissed',
        ];
    }
}
