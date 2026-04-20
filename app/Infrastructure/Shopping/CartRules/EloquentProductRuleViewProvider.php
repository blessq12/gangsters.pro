<?php

namespace App\Infrastructure\Shopping\CartRules;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Shopping\CartRules\Contracts\ProductRuleViewProviderInterface;
use App\Domain\Shopping\CartRules\ProductRuleView;
use App\Infrastructure\Product\Model\PRD_Product;

final class EloquentProductRuleViewProvider implements ProductRuleViewProviderInterface
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function getViewsByProductIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $productIds = array_values(array_unique(array_filter($productIds, static fn (int $id) => $id > 0)));
        $entities = $this->products->findActiveByIds($productIds);
        $out = [];
        foreach ($entities as $product) {
            $id = $product->id();
            if ($id === null) {
                continue;
            }
            $price = $product->price();
            if ($price === null || $price < 1) {
                continue;
            }
            $out[$id] = new ProductRuleView(
                $id,
                $price,
                $product->cartRuleCountsAsRoll(),
                $product->cartRuleGiftCandidate(),
                $product->cartRuleIsComplementSet(),
            );
        }

        return $out;
    }

    public function findActiveGiftCandidateProductIds(): array
    {
        return PRD_Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where('cart_rule_gift_candidate', true)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    public function findActiveComplementSetProductIds(): array
    {
        return PRD_Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where('cart_rule_is_complement_set', true)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }
}
