<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Marketing\Entity\StorefrontDisplayPrice;
use App\Domain\Marketing\Repository\StorefrontDisplayPriceRepository;
use App\Infrastructure\Catalog\Model\PRD_Product;

/**
 * Витринная «старая» цена: читает колонку old_price (рубли) из PRD_Product.
 * Физический вынос колонки — отдельный план миграций.
 */
final class EloquentStorefrontDisplayPriceRepository implements StorefrontDisplayPriceRepository
{
    private const KOPECKS_IN_RUBLE = 100;

    public function findByCatalogItemIds(array $ids): array
    {
        $normalized = [];
        foreach ($ids as $id) {
            if (is_numeric($id) && (int) $id > 0) {
                $normalized[(int) $id] = (int) $id;
            }
        }

        if ($normalized === []) {
            return [];
        }

        $prices = [];
        $rows = PRD_Product::query()
            ->whereIn('id', array_values($normalized))
            ->get(['id', 'old_price']);

        foreach ($rows as $row) {
            $oldPriceRubles = $row->old_price !== null ? (int) $row->old_price : 0;
            $prices[(int) $row->id] = new StorefrontDisplayPrice(
                catalogItemId: (int) $row->id,
                oldPriceKopecks: $oldPriceRubles > 0 ? $oldPriceRubles * self::KOPECKS_IN_RUBLE : null,
            );
        }

        return $prices;
    }
}
