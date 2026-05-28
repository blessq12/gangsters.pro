<?php

namespace App\Domain\SystemContent\ValueObjects;

final readonly class CompanyDeliveryTerms
{
    public function __construct(
        public ?int $freeDeliveryThresholdKopecks,
        public ?int $deliveryFeeKopecks,
    ) {}
}
