<?php

namespace App\Domain\Promotion\Port;

use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Shared\Enum\DeliveryMethod;

interface PromotionDeliveryPricingPort
{
    public function resolveInZone(?float $latitude, ?float $longitude): ?bool;

    public function resolveFreeDeliveryThresholdKopecks(): ?int;

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        ?bool $inZone,
    ): int;
}
