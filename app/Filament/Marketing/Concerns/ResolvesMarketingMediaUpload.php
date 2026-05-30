<?php

namespace App\Filament\Marketing\Concerns;

use App\Application\Marketing\Contracts\MarketingMediaStoragePort;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ResolvesMarketingMediaUpload
{
    protected function resolveMarketingMediaUpload(
        mixed $upload,
        ?string $existing,
        string $directory,
    ): ?string {
        $file = $this->extractMarketingUploadedFile($upload);
        if ($file !== null) {
            return app(MarketingMediaStoragePort::class)->store($file, $directory);
        }

        $storedPath = $this->extractMarketingStoredPath($upload);
        if ($storedPath !== null && $this->isMarketingMediaPath($storedPath, $directory)) {
            return $storedPath;
        }

        return $existing;
    }

    private function extractMarketingUploadedFile(mixed $upload): ?TemporaryUploadedFile
    {
        if ($upload instanceof TemporaryUploadedFile) {
            return $upload;
        }

        if (is_array($upload)) {
            foreach ($upload as $item) {
                if ($item instanceof TemporaryUploadedFile) {
                    return $item;
                }
            }
        }

        return null;
    }

    private function extractMarketingStoredPath(mixed $upload): ?string
    {
        if (is_string($upload) && $upload !== '') {
            return $upload;
        }

        if (is_array($upload)) {
            foreach ($upload as $item) {
                if (is_string($item) && $item !== '') {
                    return $item;
                }
            }
        }

        return null;
    }

    private function isMarketingMediaPath(string $path, string $directory): bool
    {
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
            return false;
        }

        $normalizedDirectory = trim($directory, '/');

        return $normalizedPath === $normalizedDirectory
            || str_starts_with($normalizedPath, $normalizedDirectory.'/');
    }
}
