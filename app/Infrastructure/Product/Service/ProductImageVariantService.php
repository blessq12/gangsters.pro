<?php

namespace App\Infrastructure\Product\Service;

final class ProductImageVariantService
{
    /**
     * @return array{
     *   thumb: array{path: string, width: int, height: int},
     *   medium: array{path: string, width: int, height: int},
     *   large: array{path: string, width: int, height: int}
     * }|null
     */
    public function generateVariants(string $thumbPath): ?array
    {
        $relativePath = ltrim($thumbPath, '/');
        $fullPath = storage_path('app/public/'.$relativePath);

        if (! is_file($fullPath)) {
            return null;
        }

        $size = @getimagesize($fullPath);
        if (! is_array($size) || ! isset($size[0], $size[1])) {
            return null;
        }

        $width = (int) $size[0];
        $height = (int) $size[1];

        return [
            'thumb' => ['path' => $thumbPath, 'width' => $width, 'height' => $height],
            'medium' => ['path' => $thumbPath, 'width' => $width, 'height' => $height],
            'large' => ['path' => $thumbPath, 'width' => $width, 'height' => $height],
        ];
    }
}
