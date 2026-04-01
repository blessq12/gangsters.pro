<?php

namespace App\Services\Order\Complimentary;

use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Infrastructure\Order\Repository\ComplimentaryItemRuleRepository;

class PerOrderByCategoryPolicy implements ComplimentaryPolicyInterface
{
    public function __construct(
        private readonly ComplimentaryItemRuleRepository $rules,
    ) {
    }

    /**
     * @param array<int, int> $cartProductIds
     * @return array<int, array{rule_id: int, trigger_category_id: int, gift_product_id: int, priority: int}>
     */
    public function resolve(array $cartProductIds): array
    {
        if ($cartProductIds === []) {
            return [];
        }

        $triggerCategoryIds = PRD_CategoryProduct::query()
            ->whereIn('product_id', $cartProductIds)
            ->distinct()
            ->pluck('category_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        return $this->rules->findActiveByTriggerCategoryIds($triggerCategoryIds);
    }
}
