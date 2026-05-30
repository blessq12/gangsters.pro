<?php

namespace App\Infrastructure\Catalog\Storage;

use App\Application\Catalog\Contracts\ProductImageStoragePort;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class LocalProductImageStorage implements ProductImageStoragePort
{
    public function storeThumb(UploadedFile $file, int $productId): array
    {
        $path = $file->store('products/'.$productId, 'public');
        $fullPath = storage_path('app/public/'.$path);
        $size = @getimagesize($fullPath);
        $width = is_array($size) ? (int) ($size[0] ?? 0) : 0;
        $height = is_array($size) ? (int) ($size[1] ?? 0) : 0;

        return [
            'path' => $path,
            'width' => $width,
            'height' => $height,
        ];
    }
}
