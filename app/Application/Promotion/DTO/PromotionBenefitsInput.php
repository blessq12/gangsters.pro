<?php

namespace App\Application\Promotion\DTO;

final readonly class PromotionBenefitsInput
{
    /**
     * @param  list<array<string, mixed>>  $giftCandidates
     * @param  list<array<string, mixed>>  $complementCandidates
     */
    public function __construct(
        public int $currentKopecks,
        public string $orderChannel,
        public ?string $deliveryMethod,
        public int $rollCount,
        public ?int $selectedGiftProductId,
        public array $giftCandidates,
        public array $complementCandidates,
        public ?float $deliveryLatitude = null,
        public ?float $deliveryLongitude = null,
    ) {}
}
