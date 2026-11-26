<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class DeleteCatalogItemImageUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $itemId, int $imageId): array
    {
        $this->items->deleteItemImage($itemId, $imageId);

        return ['ok' => true];
    }
}
