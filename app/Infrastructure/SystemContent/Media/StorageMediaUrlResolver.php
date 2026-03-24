<?php

namespace App\Infrastructure\SystemContent\Media;

use App\Shared\SystemContent\MediaUrlResolver;
use Illuminate\Support\Facades\Storage;

final class StorageMediaUrlResolver implements MediaUrlResolver
{
    public function resolve(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('media')->url($path);
    }
}

