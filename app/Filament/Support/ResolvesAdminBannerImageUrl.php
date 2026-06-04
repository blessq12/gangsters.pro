<?php

namespace App\Filament\Support;

use App\Infrastructure\SystemContent\Model\SYS_Banner;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use App\Shared\SystemContent\MediaUrlResolver;

trait ResolvesAdminBannerImageUrl
{
    protected function resolveBannerPreviewUrl(SYS_Banner|SYS_Promotion $record): ?string
    {
        $resolver = app(MediaUrlResolver::class);

        if ($record instanceof SYS_Banner) {
            $mobilePath = $record->image_mobile ?? $record->image;
            $desktopPath = $record->image_desktop ?? $record->image;
            $previewPath = $mobilePath ?? $desktopPath ?? $record->image;

            return $resolver->resolve($previewPath);
        }

        return $resolver->resolve($record->image);
    }
}
