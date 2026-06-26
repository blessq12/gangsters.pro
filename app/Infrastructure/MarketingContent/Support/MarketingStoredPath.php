<?php

namespace App\Infrastructure\MarketingContent\Support;

use Illuminate\Support\Arr;

final class MarketingStoredPath
{
    /**
     * Путь относительно public disk для Filament ImageColumn (без второго Storage::url).
     */
    public static function normalizePublicDiskPath(mixed $path): ?string
    {
        $path = self::normalizeRaw($path);

        if ($path === null) {
            return null;
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return null;
        }

        if (str_starts_with($path, '/images/')) {
            return null;
        }

        if (str_starts_with($path, '/') && ! str_starts_with($path, '/storage/')) {
            return null;
        }

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $path = ltrim($path, '/');

        return $path !== '' ? $path : null;
    }

    /**
     * State для Filament: disk-relative или абсолютный URL (сидер /images, вне storage).
     */
    public static function filamentImageState(?string $path): ?string
    {
        $diskPath = self::normalizePublicDiskPath($path);

        if ($diskPath !== null) {
            return $diskPath;
        }

        $resolved = PublicMediaUrl::resolve($path);

        if ($resolved === null) {
            return null;
        }

        if (filter_var($resolved, FILTER_VALIDATE_URL) !== false) {
            return $resolved;
        }

        return url($resolved);
    }

    private static function normalizeRaw(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = Arr::first($path);
        }

        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, '{') || str_starts_with($path, '[')) {
            $decoded = json_decode($path, true);

            if (is_array($decoded)) {
                $first = Arr::first($decoded);

                return is_string($first) && $first !== '' ? $first : null;
            }
        }

        return $path;
    }
}
