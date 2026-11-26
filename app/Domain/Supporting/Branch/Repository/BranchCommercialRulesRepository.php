<?php

namespace App\Domain\Supporting\Branch\Repository;

use App\Domain\Core\Order\Entity\OrderCommercialRules;

interface BranchCommercialRulesRepository
{
    public function findByBranch(int $branchId): ?OrderCommercialRules;

    /**
     * @param  array{
     *     gift_pickup_min_order_kopecks: int,
     *     gift_courier_min_order_kopecks: int,
     *     gift_courier_weekdays: list<int>,
     *     gift_benefit_active: bool,
     *     delivery_free_threshold_kopecks: int,
     *     delivery_outside_zone_surcharge_kopecks: int,
     *     delivery_benefit_active: bool,
     *     complement_set_benefit_active: bool,
     *     complement_set_rolls_per_set: int,
     *     complement_set_max_sets: int
     * }  $fields
     */
    public function saveForBranch(int $branchId, array $fields): OrderCommercialRules;
}
