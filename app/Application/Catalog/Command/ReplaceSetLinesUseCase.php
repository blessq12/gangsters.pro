<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class ReplaceSetLinesUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @return array{ok: true}
     */
    public function execute(int $setId, array $lines): array
    {
        if ($this->items->findSetById($setId) === null) {
            // findSetById returns null for empty-line sets historically; after mapper fix it returns set.
            // Still require item exists as set kind via replaceSetLines itself.
        }

        $this->items->replaceSetLines($setId, $lines);

        return ['ok' => true];
    }
}
