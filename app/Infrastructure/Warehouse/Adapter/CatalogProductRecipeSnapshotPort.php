<?php

namespace App\Infrastructure\Warehouse\Adapter;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\TechCard;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\Repository\TechCardRepository;
use App\Domain\Supporting\Menu\Entity\BranchSet;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;
use App\Domain\Supporting\Warehouse\Port\ProductRecipeSnapshotPort;
use Illuminate\Support\Facades\Log;

/**
 * ACL: раскрытие техкарт Catalog (+ сеты Menu) в потребности ингредиентов для Warehouse.
 */
final class CatalogProductRecipeSnapshotPort implements ProductRecipeSnapshotPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalog,
        private readonly TechCardRepository $techCards,
        private readonly BranchSetRepository $branchSets,
    ) {}

    public function resolveNeeds(int $branchId, array $lines): array
    {
        /** @var array<string, array{ingredient_ref: string, quantity: float, unit: string}> $aggregated */
        $aggregated = [];

        foreach ($lines as $line) {
            $entryId = (int) ($line['entry_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            if ($entryId < 1 || $quantity < 1) {
                continue;
            }

            foreach ($this->needsForEntry($branchId, $entryId, (float) $quantity) as $need) {
                $key = $need['ingredient_ref'].'|'.$need['unit'];
                if (! isset($aggregated[$key])) {
                    $aggregated[$key] = $need;

                    continue;
                }
                $aggregated[$key]['quantity'] += $need['quantity'];
            }
        }

        return array_values($aggregated);
    }

    /**
     * @return list<array{ingredient_ref: string, quantity: float, unit: string}>
     */
    private function needsForEntry(int $branchId, int $entryId, float $lineQuantity): array
    {
        $product = $this->catalog->findProductById($entryId);
        if ($product instanceof Product) {
            return $this->scaleCard(
                $this->techCards->findByProductId($entryId),
                $lineQuantity,
                $entryId,
            );
        }

        $set = $this->branchSets->findById($branchId, $entryId);
        if (! $set instanceof BranchSet) {
            Log::info('Warehouse recipe: entry not product or set', [
                'branch_id' => $branchId,
                'entry_id' => $entryId,
            ]);

            return [];
        }

        $needs = [];
        $memberIds = [];
        foreach ($set->lines() as $setLine) {
            $memberIds[] = $setLine->catalogItemId();
        }
        $cards = $this->techCards->findByProductIds($memberIds);

        foreach ($set->lines() as $setLine) {
            $memberId = $setLine->catalogItemId();
            $qty = $lineQuantity * (float) $setLine->quantity();
            $card = $cards[$memberId] ?? null;
            foreach ($this->scaleCard($card, $qty, $memberId) as $need) {
                $needs[] = $need;
            }
        }

        return $needs;
    }

    /**
     * @return list<array{ingredient_ref: string, quantity: float, unit: string}>
     */
    private function scaleCard(?TechCard $card, float $multiplier, int $productId): array
    {
        if (! $card instanceof TechCard || $card->isEmpty()) {
            Log::info('Warehouse recipe: no tech card for product', [
                'product_id' => $productId,
            ]);

            return [];
        }

        $needs = [];
        foreach ($card->lines() as $line) {
            $needs[] = [
                'ingredient_ref' => $line->ingredientRef(),
                'quantity' => $line->quantity() * $multiplier,
                'unit' => $line->unit(),
            ];
        }

        return $needs;
    }
}
