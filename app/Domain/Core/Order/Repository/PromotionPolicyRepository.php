<?php

namespace App\Domain\Core\Order\Repository;

use App\Domain\Core\Order\Policy\PromotionPolicy;

interface PromotionPolicyRepository
{
    public function findByBranch(int $branchId): ?PromotionPolicy;

    /**
     * @param  array{
     *     gift_pickup_min_order_kopecks: int,
     *     gift_courier_min_order_kopecks: int,
     *     gift_courier_weekdays: list<int>,
     *     gift_benefit_active: bool,
     *     gift_product_id: int|null,
     *     delivery_free_threshold_kopecks: int,
     *     delivery_outside_zone_surcharge_kopecks: int,
     *     delivery_benefit_active: bool,
     *     complement_set_benefit_active: bool,
     *     complement_set_rolls_per_set: int,
     *     complement_set_max_sets: int
     * }  $fields
     */
    public function saveForBranch(int $branchId, array $fields): PromotionPolicy;
}
