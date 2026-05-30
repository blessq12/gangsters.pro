<?php

namespace App\Filament\Marketing\Concerns;

trait ResolvesMarketingPromotionUploads
{
    use ResolvesMarketingMediaUpload;

    private const PROMOTION_MEDIA_DIRECTORY = 'marketing/promotions';

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolvePromotionImagePath(array $data, ?string $existing): ?string
    {
        return $this->resolveMarketingMediaUpload(
            $data['image_upload'] ?? null,
            $existing,
            self::PROMOTION_MEDIA_DIRECTORY,
        );
    }
}
