<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Marketing\Entity\StorefrontDisplayPrice;
use App\Domain\Marketing\Repository\StorefrontDisplayPriceRepository;
use App\Infrastructure\Catalog\Model\CAT_Price;

/**
 * Витринная «старая» цена: читает CAT_prices type=old (копейки).
 * Кросс-контекст без FK — только opaque item_id.
 */
final class EloquentStorefrontDisplayPriceRepository implements StorefrontDisplayPriceRepository
{
    private const PRICE_TYPE_OLD = 'old';

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
        foreach ($normalized as $id) {
            $prices[$id] = new StorefrontDisplayPrice(
                catalogItemId: $id,
                oldPriceKopecks: null,
            );
        }

        $rows = CAT_Price::query()
            ->where('type', self::PRICE_TYPE_OLD)
            ->whereIn('item_id', array_values($normalized))
            ->get(['item_id', 'amount_kopecks']);

        foreach ($rows as $row) {
            $amount = max(0, (int) $row->amount_kopecks);
            $prices[(int) $row->item_id] = new StorefrontDisplayPrice(
                catalogItemId: (int) $row->item_id,
                oldPriceKopecks: $amount > 0 ? $amount : null,
            );
        }

        return $prices;
    }
}
