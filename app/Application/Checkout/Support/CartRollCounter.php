<?php

namespace App\Application\Checkout\Support;

use App\Domain\Checkout\Port\CatalogRollMetaPort;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;

final class CartRollCounter
{
    public function __construct(
        private readonly CatalogRollMetaPort $rollMeta,
    ) {}

    /**
     * @param  list<CartLineSnapshot>  $userLines
     */
    public function countRollUnits(array $userLines): int
    {
        if ($userLines === []) {
            return 0;
        }

        $productIds = array_map(
            static fn (CartLineSnapshot $line): int => $line->productId(),
            $userLines,
        );

        $countsAsRollByProductId = $this->rollMeta->countsAsRollByProductIds($productIds);
        $rollCount = 0;

        foreach ($userLines as $line) {
            if (! ($countsAsRollByProductId[$line->productId()] ?? false)) {
                continue;
            }

            $rollCount += $line->quantity();
        }

        return $rollCount;
    }
}
