<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

final class ProductImageVariantService
{
    /** Макс. сторона для thumb (сохраняем пропорции). */
    private const THUMB_MAX_SIDE = 300;

    private const MEDIUM_MAX_SIDE = 800;

    private const LARGE_MAX_SIDE = 1200;

    /** Качество JPEG (0–100), меньше — меньше вес. */
    private const JPEG_QUALITY = 82;

    /**
     * Генерирует три варианта изображения (thumb, medium, large) из одного файла.
     * Путь — относительный от storage/app/public (например products/xxx.png).
     *
     * @return array{thumb: array{path: string, width: int, height: int}, medium: array{path: string, width: int, height: int}, large: array{path: string, width: int, height: int}}|null
     */
    public function generateVariants(string $relativePath): ?array
    {
        $relativePath = ltrim($relativePath, '/');
        $fullPath = storage_path('app/public/'.$relativePath);

        if (! is_file($fullPath) || ! is_readable($fullPath)) {
            return null;
        }

        $pathInfo = pathinfo($relativePath);
        $dir = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? 'image';
        $extension = $pathInfo['extension'] ?? 'jpg';
        $baseDir = $dir ? $dir.'/' : '';

        $thumbPath = $baseDir.$filename.'-thumb.'.$extension;
        $mediumPath = $baseDir.$filename.'-medium.'.$extension;
        $largePath = $baseDir.$filename.'-large.'.$extension;

        $thumbFull = storage_path('app/public/'.$thumbPath);
        $mediumFull = storage_path('app/public/'.$mediumPath);
        $largeFull = storage_path('app/public/'.$largePath);

        $isJpeg = in_array(strtolower($extension), ['jpg', 'jpeg'], true);

        try {
            $thumbImg = Image::make($fullPath);
            $this->resizeMaxSide($thumbImg, self::THUMB_MAX_SIDE);
            $this->saveWithCompression($thumbImg, $thumbFull, $isJpeg);
            $thumbW = (int) $thumbImg->width();
            $thumbH = (int) $thumbImg->height();
            $thumbImg->destroy();
        } catch (\Throwable) {
            return null;
        }

        try {
            $mediumImg = Image::make($fullPath);
            $this->resizeMaxSide($mediumImg, self::MEDIUM_MAX_SIDE);
            $this->saveWithCompression($mediumImg, $mediumFull, $isJpeg);
            $mediumW = (int) $mediumImg->width();
            $mediumH = (int) $mediumImg->height();
            $mediumImg->destroy();
        } catch (\Throwable) {
            File::delete($thumbFull);
            return null;
        }

        try {
            $largeImg = Image::make($fullPath);
            $this->resizeMaxSide($largeImg, self::LARGE_MAX_SIDE);
            $this->saveWithCompression($largeImg, $largeFull, $isJpeg);
            $largeW = (int) $largeImg->width();
            $largeH = (int) $largeImg->height();
            $largeImg->destroy();
        } catch (\Throwable) {
            File::delete([$thumbFull, $mediumFull]);
            return null;
        }

        if ($fullPath !== $thumbFull && $fullPath !== $mediumFull && $fullPath !== $largeFull) {
            File::delete($fullPath);
        }

        return [
            'thumb' => ['path' => $thumbPath, 'width' => $thumbW, 'height' => $thumbH],
            'medium' => ['path' => $mediumPath, 'width' => $mediumW, 'height' => $mediumH],
            'large' => ['path' => $largePath, 'width' => $largeW, 'height' => $largeH],
        ];
    }

    private function resizeMaxSide(\Intervention\Image\Image $img, int $maxSide): void
    {
        $w = $img->width();
        $h = $img->height();
        if ($w <= $maxSide && $h <= $maxSide) {
            return;
        }
        if ($w >= $h) {
            $img->resize($maxSide, null, function ($constraint): void {
                $constraint->aspectRatio();
            });
        } else {
            $img->resize(null, $maxSide, function ($constraint): void {
                $constraint->aspectRatio();
            });
        }
    }

    private function saveWithCompression(\Intervention\Image\Image $img, string $fullPath, bool $isJpeg): void
    {
        if ($isJpeg) {
            $img->save($fullPath, self::JPEG_QUALITY);
        } else {
            $img->save($fullPath);
        }
    }
}
