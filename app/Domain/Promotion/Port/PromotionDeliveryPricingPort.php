<?php

namespace App\Domain\Promotion\Port;

use App\Domain\Promotion\Entity\PromotionPolicy;

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
