<?php

namespace App\Application\Catalog\Query;

use App\Domain\Catalog\Repository\CatalogItemRepository;

final class ListProductImagesUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return list<array{id: int, url: string, focus_x: float|null, focus_y: float|null}>
     */
    public function execute(int $productId): array
    {
        return $this->items->listProductImages($productId);
    }
}
