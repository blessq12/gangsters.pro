<?php

namespace App\Application\Promotion\DTO;

use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\ValueObject\BenefitProductCandidate;
use App\Shared\Enum\DeliveryMethod;

final readonly class PromotionBenefitsInput
{
    /**
     * @param  list<BenefitProductCandidate>  $giftCandidates
     * @param  list<BenefitProductCandidate>  $complementCandidates
     */
    public function __construct(
        public int $currentKopecks,
        public PromotionOrderChannel $orderChannel,
        public ?DeliveryMethod $deliveryMethod,
        public int $rollCount,
        public ?int $selectedGiftProductId,
        public array $giftCandidates,
        public array $complementCandidates,
        public ?float $deliveryLatitude = null,
        public ?float $deliveryLongitude = null,
    ) {}
}
