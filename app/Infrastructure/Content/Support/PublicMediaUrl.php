<?php

namespace App\Infrastructure\Content\Support;

use Illuminate\Support\Facades\Storage;

final class PublicMediaUrl
{
    public static function resolve(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            return $trimmed;
        }

        $diskPath = MarketingStoredPath::normalizePublicDiskPath($trimmed);

        if ($diskPath !== null) {
            return Storage::disk('public')->url($diskPath);
        }

        if (str_starts_with($trimmed, '/')) {
            return asset($trimmed);
        }

        return asset('/'.$trimmed);
    }
}
