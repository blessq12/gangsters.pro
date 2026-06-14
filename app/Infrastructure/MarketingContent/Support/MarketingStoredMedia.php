<?php

namespace App\Infrastructure\MarketingContent\Support;

use Illuminate\Support\Facades\Storage;

final class MarketingStoredMedia
{
    public static function deleteIfStored(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (str_starts_with($path, '/') || preg_match('#^https?://#i', $path) === 1) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
