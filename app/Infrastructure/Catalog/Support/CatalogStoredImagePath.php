<?php

namespace App\Infrastructure\Catalog\Support;

use Illuminate\Support\Arr;

final class CatalogStoredImagePath
{
    public static function normalize(mixed $path): ?string
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
