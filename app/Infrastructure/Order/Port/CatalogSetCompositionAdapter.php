<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\ValueObject\ProductSetLine;
use App\Domain\Order\Port\CatalogSetCompositionLine;
use App\Domain\Order\Port\CatalogSetCompositionPort;

final class CatalogSetCompositionAdapter implements CatalogSetCompositionPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    public function findActiveCompositionsBySetIds(array $setIds): array
    {
        $normalizedIds = array_values(array_unique(array_map(
            static fn (mixed $id): int => (int) $id,
            $setIds,
        )));

        $normalizedIds = array_values(array_filter(
            $normalizedIds,
            static fn (int $id): bool => $id > 0,
        ));

        if ($normalizedIds === []) {
            return [];
        }

        $sets = $this->catalogItems->findActiveSetsByIds($normalizedIds);
        $compositions = [];

        foreach ($sets as $set) {
            if (! $set instanceof ProductSet) {
                continue;
            }

            $compositions[$set->id()] = array_map(
                static fn (ProductSetLine $line): CatalogSetCompositionLine => new CatalogSetCompositionLine(
                    productId: $line->productId(),
                    quantity: $line->quantity(),
                ),
                $set->lines(),
            );
        }

        return $compositions;
    }
}
