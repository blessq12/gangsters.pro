<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Order\Port\CatalogRollMetaPort;
use App\Infrastructure\Catalog\Model\PRD_Product;

final class CatalogRollMetaAdapter implements CatalogRollMetaPort
{
    public function countsAsRollByProductIds(array $productIds): array
    {
        $normalizedIds = array_values(array_unique(array_map(
            static fn (mixed $id): int => (int) $id,
            $productIds,
        )));

        $normalizedIds = array_values(array_filter(
            $normalizedIds,
            static fn (int $id): bool => $id > 0,
        ));

        if ($normalizedIds === []) {
            return [];
        }

        $flags = PRD_Product::query()
            ->whereIn('id', $normalizedIds)
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->pluck('meta_counts_as_roll', 'id');

        $result = [];

        foreach ($normalizedIds as $productId) {
            $result[$productId] = (bool) ($flags[$productId] ?? false);
        }

        return $result;
    }
}
