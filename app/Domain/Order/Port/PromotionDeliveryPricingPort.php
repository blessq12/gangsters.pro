<?php

namespace App\Domain\Order\Port;

use App\Domain\Content\ValueObject\DeliveryZone;
use App\Domain\Order\Entity\PromotionPolicy;

interface PromotionDeliveryPricingPort
{
    public function resolveZone(?float $latitude, ?float $longitude): ?DeliveryZone;

    public function resolveFreeDeliveryThresholdKopecks(): ?int;

    public function resolveKitchenCity(): ?string;

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?string $deliveryMethod,
        int $currentKopecks,
        ?DeliveryZone $zone,
    ): int;
}
