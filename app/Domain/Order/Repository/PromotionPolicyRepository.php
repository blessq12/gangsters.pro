<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\PromotionPolicy;

interface PromotionPolicyRepository
{
    public const SINGLETON_ID = 1;

    public function find(): ?PromotionPolicy;

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
    public function save(array $fields): PromotionPolicy;
}
