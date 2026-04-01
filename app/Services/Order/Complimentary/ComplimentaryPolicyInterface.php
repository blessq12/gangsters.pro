<?php

namespace App\Services\Order\Complimentary;

interface ComplimentaryPolicyInterface
{
    /**
     * @param array<int, int> $cartProductIds
     * @return array<int, array{rule_id: int, trigger_category_id: int, gift_product_id: int, priority: int}>
     */
    public function resolve(array $cartProductIds): array;
}
