<?php

namespace App\Domain\Promotion\Port;

use App\Domain\Delivery\ValueObject\DeliveryZone;
use App\Domain\Promotion\Entity\PromotionPolicy;

/**
 * Расчёт стоимости доставки по коммерческим правилам акции.
 */
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
