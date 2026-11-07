<?php

namespace App\Application\Catalog\Command;

use App\Domain\Catalog\Repository\CatalogItemRepository;

final class AddProductImageUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    public function execute(int $productId, string $path): void
    {
        $this->items->addProductImage($productId, $path);
    }
}
