<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class UpdateCatalogItemImageUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}
     */
    public function execute(
        int $itemId,
        int $imageId,
        ?float $focusX = null,
        ?float $focusY = null,
        ?int $sortOrder = null,
    ): array {
        return $this->items->updateItemImage($itemId, $imageId, $focusX, $focusY, $sortOrder);
    }
}
