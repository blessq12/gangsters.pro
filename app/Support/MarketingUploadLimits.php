<?php

namespace App\Support;

final class MarketingUploadLimits
{
    public static function hasAppCap(string $scope): bool
    {
        return self::configMaxKb($scope) > 0;
    }

    public static function effectiveMaxKb(string $scope): int
    {
        $configKb = self::configMaxKb($scope);
        $phpKb = self::phpUploadMaxKb();

        if ($configKb <= 0) {
            return $phpKb;
        }

        return min($configKb, $phpKb);
    }

    public static function maxLivewireUploadKb(): int
    {
        return max(
            self::effectiveMaxKb('banner'),
            self::effectiveMaxKb('promotion'),
        );
    }

    /**
     * @return list<string>
     */
    public static function livewireTemporaryUploadRules(): array
    {
        if (! self::hasAppCap('banner') && ! self::hasAppCap('promotion')) {
            return ['file'];
        }

        return ['file', 'max:'.self::maxLivewireUploadKb()];
    }

    public static function shouldApplyFilamentMaxSize(string $scope): bool
    {
        return self::hasAppCap($scope);
    }

    public static function filamentMaxSizeKb(string $scope): int
    {
        return self::effectiveMaxKb($scope);
    }

    public static function helperText(string $scope): string
    {
        $phpLabel = PhpIniSize::toMegabytesLabel(ini_get('upload_max_filesize') ?: 'unknown');
        $configKb = self::configMaxKb($scope);

        if ($configKb <= 0) {
            return "Лимит одного файла — {$phpLabel} (настройка PHP upload_max_filesize). Имя файла может быть любым.";
        }

        $effectiveMb = max(1, (int) round(self::effectiveMaxKb($scope) / 1024));

        $text = "До {$effectiveMb} MB на файл (лимит приложения). Имя файла может быть любым.";

        if (self::effectiveMaxKb($scope) < $configKb) {
            $text .= " Сервер PHP сейчас принимает максимум {$phpLabel} на один файл"
                .' — увеличьте upload_max_filesize (см. docs/admin-media-upload-limits.md).';
        }

        return $text;
    }

    private static function configMaxKb(string $scope): int
    {
        return (int) config("marketing.{$scope}.max_upload_kb", 0);
    }

    private static function phpUploadMaxKb(): int
    {
        $bytes = PhpIniSize::toBytes(ini_get('upload_max_filesize') ?: '2M');

        if ($bytes >= PHP_INT_MAX / 2) {
            return (int) (PHP_INT_MAX / 1024);
        }

        return max(1, (int) floor($bytes / 1024));
    }
}
