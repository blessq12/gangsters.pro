<?php

namespace App\Application\Order\OrderDraft\Support;

use App\Domain\Order\Port\CatalogRollMetaPort;
use App\Domain\Order\Port\CatalogSetCompositionLine;
use App\Domain\Order\Port\CatalogSetCompositionPort;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;

final class CartRollCounter
{
    public function __construct(
        private readonly CatalogRollMetaPort $rollMeta,
        private readonly CatalogSetCompositionPort $setComposition,
    ) {}

    /**
     * @param  list<CartLineSnapshot>  $userLines
     */
    public function countRollUnits(array $userLines): int
    {
        if ($userLines === []) {
            return 0;
        }

        $lineProductIds = array_map(
            static fn (CartLineSnapshot $line): int => $line->productId(),
            $userLines,
        );

        $setCompositions = $this->setComposition->findActiveCompositionsBySetIds($lineProductIds);
        $componentProductIds = $this->collectComponentProductIds($setCompositions);
        $countsAsRollByProductId = $this->rollMeta->countsAsRollByProductIds([
            ...$lineProductIds,
            ...$componentProductIds,
        ]);

        $rollCount = 0;

        foreach ($userLines as $line) {
            $composition = $setCompositions[$line->productId()] ?? null;

            if ($composition !== null) {
                $rollCount += $this->countRollUnitsInSetLine($line->quantity(), $composition, $countsAsRollByProductId);

                continue;
            }

            if ($countsAsRollByProductId[$line->productId()] ?? false) {
                $rollCount += $line->quantity();
            }
        }

        return $rollCount;
    }

    /**
     * @param  array<int, list<CatalogSetCompositionLine>>  $setCompositions
     * @return list<int>
     */
    private function collectComponentProductIds(array $setCompositions): array
    {
        $productIds = [];

        foreach ($setCompositions as $composition) {
            foreach ($composition as $line) {
                $productIds[] = $line->productId();
            }
        }

        return array_values(array_unique($productIds));
    }

    /**
     * @param  list<CatalogSetCompositionLine>  $composition
     * @param  array<int, bool>  $countsAsRollByProductId
     */
    private function countRollUnitsInSetLine(
        int $setQuantity,
        array $composition,
        array $countsAsRollByProductId,
    ): int {
        $rollCount = 0;

        foreach ($composition as $component) {
            if (! ($countsAsRollByProductId[$component->productId()] ?? false)) {
                continue;
            }

            $rollCount += $setQuantity * $component->quantity();
        }

        return $rollCount;
    }
}
