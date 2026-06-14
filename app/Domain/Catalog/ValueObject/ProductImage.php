<?php

namespace App\Domain\Catalog\ValueObject;

/**
 * Изображение товара в каталоге (storage-relative path).
 */
final class ProductImage
{
    public function __construct(
        private readonly string $path,
        private readonly int $sortOrder,
    ) {}

    public function path(): string
    {
        return $this->path;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
