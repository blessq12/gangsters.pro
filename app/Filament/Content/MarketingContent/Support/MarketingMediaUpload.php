<?php

namespace App\Filament\Content\MarketingContent\Support;

use Filament\Forms\Components\FileUpload;

final class MarketingMediaUpload
{
    private const DISK = 'public';

    private const ACCEPTED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public static function bannerDesktop(): FileUpload
    {
        return self::make(
            name: 'image_desktop',
            label: 'Изображение (десктоп)',
            directory: 'marketing/banners/desktop',
            configKey: 'banner',
        );
    }

    public static function bannerMobile(): FileUpload
    {
        return self::make(
            name: 'image_mobile',
            label: 'Изображение (мобила)',
            directory: 'marketing/banners/mobile',
            configKey: 'banner',
        );
    }

    public static function promotionImage(): FileUpload
    {
        return self::make(
            name: 'image',
            label: 'Изображение',
            directory: 'marketing/promotions',
            configKey: 'promotion',
            previewHeight: null,
        )
            ->panelLayout('integrated')
            ->panelAspectRatio(null);
    }

    private static function make(
        string $name,
        string $label,
        string $directory,
        string $configKey,
        ?string $previewHeight = '150',
    ): FileUpload {
        $field = FileUpload::make($name)
            ->label($label)
            ->image()
            ->disk(self::DISK)
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes(self::ACCEPTED_TYPES)
            ->openable()
            ->downloadable();

        if ($previewHeight !== null) {
            $field->imagePreviewHeight($previewHeight);
        }

        $maxUploadKb = self::maxUploadKilobytes($configKey);

        if ($maxUploadKb !== null) {
            $field->maxSize($maxUploadKb);
        }

        return $field;
    }

    private static function maxUploadKilobytes(string $configKey): ?int
    {
        $kb = (int) config("marketing.{$configKey}.max_upload_kb", 0);

        return $kb > 0 ? $kb : null;
    }
}
