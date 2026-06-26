<?php

namespace App\Infrastructure\MarketingContent\Support;

use Illuminate\Support\Facades\Storage;

final class MarketingStoredMedia
{
    public static function deleteIfStored(?string $path): void
    {
        $diskPath = MarketingStoredPath::normalizePublicDiskPath($path);

        if ($diskPath === null) {
            return;
        }

        Storage::disk('public')->delete($diskPath);
    }
}
