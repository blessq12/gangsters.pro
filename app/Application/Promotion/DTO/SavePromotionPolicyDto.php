<?php

namespace App\Application\Promotion\DTO;

final readonly class SavePromotionPolicyDto
{
    /**
     * @param  list<int>  $giftCourierWeekdays
     */
    public function __construct(
        public int $branchId,
        public int $giftPickupMinOrderKopecks,
        public int $giftCourierMinOrderKopecks,
        public array $giftCourierWeekdays,
        public bool $giftBenefitActive,
        public int $deliveryFreeThresholdKopecks,
        public bool $deliveryBenefitActive,
        public bool $complementSetBenefitActive,
        public int $complementSetRollsPerSet,
        public int $complementSetMaxSets,
    ) {}
}
