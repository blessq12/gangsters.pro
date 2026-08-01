<?php

namespace App\Domain\Order\Port;

use App\Domain\Order\Entity\PromotionPolicy;

interface PromotionDeliveryPricingPort
{
    public function resolveInZone(?float $latitude, ?float $longitude): ?bool;

    public function resolveFreeDeliveryThresholdKopecks(): ?int;

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?string $deliveryMethod,
        int $currentKopecks,
        ?bool $inZone,
    ): int;
}
