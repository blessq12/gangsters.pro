<?php

namespace App\Filament\Marketing\Concerns;

trait ResolvesMarketingBannerUploads
{
    use ResolvesMarketingMediaUpload;

    private const BANNER_MEDIA_DIRECTORY = 'marketing/banners';

    /**
     * @param  array<string, mixed>  $data
     * @param  array{image_mobile: ?string, image_desktop: ?string}  $existing
     * @return array{image_mobile: ?string, image_desktop: ?string}
     */
    protected function resolveBannerImagePaths(array $data, array $existing): array
    {
        return [
            'image_mobile' => $this->resolveMarketingMediaUpload(
                $data['image_mobile_upload'] ?? null,
                $existing['image_mobile'] ?? null,
                self::BANNER_MEDIA_DIRECTORY,
            ),
            'image_desktop' => $this->resolveMarketingMediaUpload(
                $data['image_desktop_upload'] ?? null,
                $existing['image_desktop'] ?? null,
                self::BANNER_MEDIA_DIRECTORY,
            ),
        ];
    }
}
