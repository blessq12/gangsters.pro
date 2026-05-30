<?php

namespace App\Infrastructure\Marketing\Storage;

use App\Application\Marketing\Contracts\MarketingMediaStoragePort;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class LocalMarketingMediaStorage implements MarketingMediaStoragePort
{
    private const DISK = 'media';

    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store(trim($directory, '/'), self::DISK);
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }
}
