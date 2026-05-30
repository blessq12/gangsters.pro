<?php

namespace App\Filament\Marketing\Support;

use App\Support\MarketingUploadLimits;
use Filament\Forms\Components\FileUpload;

final class MarketingImageUpload
{
    private const BANNER_MOBILE_HELPER = 'Соотношение сторон: 3/4 (вертикально). Рекоменд. размеры: 900×1200 или 1200×1600.';

    private const BANNER_DESKTOP_HELPER = 'Соотношение сторон: 4/3 (горизонтально). Рекоменд. размеры: 1200×900 или 1600×1200.';

    private const PROMOTION_HELPER = 'Рекомендуется использовать PNG с пустым фоном.';

    public static function bannerMobile(): FileUpload
    {
        return self::make(
            name: 'image_mobile_upload',
            label: 'Mobile',
            directory: 'marketing/banners',
            scope: 'banner',
            requiredOnCreate: true,
            helperHint: self::BANNER_MOBILE_HELPER,
        );
    }

    public static function bannerDesktop(): FileUpload
    {
        return self::make(
            name: 'image_desktop_upload',
            label: 'Desktop',
            directory: 'marketing/banners',
            scope: 'banner',
            requiredOnCreate: true,
            helperHint: self::BANNER_DESKTOP_HELPER,
        );
    }

    public static function promotion(): FileUpload
    {
        return self::make(
            name: 'image_upload',
            label: 'Изображение',
            directory: 'marketing/promotions',
            scope: 'promotion',
            requiredOnCreate: false,
            helperHint: self::PROMOTION_HELPER,
        );
    }

    private static function make(
        string $name,
        string $label,
        string $directory,
        string $scope,
        bool $requiredOnCreate,
        ?string $helperHint = null,
    ): FileUpload {
        $field = FileUpload::make($name)
            ->label($label)
            ->image()
            ->maxFiles(1)
            ->disk('media')
            ->directory($directory)
            ->visibility('public');

        if ($helperHint !== null) {
            $field->helperText($helperHint);
        } else {
            $field->helperText(fn (): string => MarketingUploadLimits::helperText($scope));
        }

        if (MarketingUploadLimits::shouldApplyFilamentMaxSize($scope)) {
            $field->maxSize(MarketingUploadLimits::filamentMaxSizeKb($scope));
        }

        if ($requiredOnCreate) {
            $field->required(fn (string $operation): bool => $operation === 'create');
        }

        return $field;
    }
}
