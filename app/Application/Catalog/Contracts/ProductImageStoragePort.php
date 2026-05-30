<?php

namespace App\Application\Catalog\Contracts;

use Illuminate\Http\UploadedFile;

interface ProductImageStoragePort
{
    /**
     * @return array{path: string, width: int, height: int}
     */
    public function storeThumb(UploadedFile $file, int $productId): array;
}
