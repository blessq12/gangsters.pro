<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\DeliveryZone;
use App\Domain\Order\Entity\PromotionPolicy;
use App\Domain\Order\Port\PromotionDeliveryPricingPort;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Shared\Geo\DeliveryZoneMatcher;

final class PromotionDeliveryPricingAdapter implements PromotionDeliveryPricingPort
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
        private readonly PromotionPolicyRepository $promotionPolicies,
    ) {}

    public function resolveZone(?float $latitude, ?float $longitude): ?DeliveryZone
    {
        $configuration = $this->deliveryConfigurations->findPublic();

        if (! $configuration instanceof DeliveryConfiguration) {
            return null;
        }

        return DeliveryZoneMatcher::match(
            $configuration->zones(),
            $latitude,
            $longitude,
        );
    }

    public function resolveFreeDeliveryThresholdKopecks(): ?int
    {
        $policy = $this->deliveryBenefit();

        if ($policy === null || ! ($policy['is_active'] ?? false)) {
            return null;
        }

        return (int) $policy['free_delivery_threshold_kopecks'];
    }

    public function resolveKitchenCity(): ?string
    {
        return $this->deliveryConfigurations->findPublic()?->kitchenAddress()->city();
    }

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?string $deliveryMethod,
        int $currentKopecks,
        ?DeliveryZone $zone,
    ): int {
        if ($deliveryMethod === 'pickup' || $deliveryMethod !== 'courier') {
            return 0;
        }

        if (! $zone instanceof DeliveryZone) {
            return 0;
        }

        $zoneFeeKopecks = max(0, $zone->deliveryFeeKopecks());
        $policy = $promotionPolicy?->deliveryBenefit();

        if (! is_array($policy) || ! ($policy['is_active'] ?? false)) {
            return $zoneFeeKopecks;
        }

        $thresholdKopecks = (int) $policy['free_delivery_threshold_kopecks'];
        $meetsThreshold = $currentKopecks >= $thresholdKopecks;

        if ($meetsThreshold && ! $zone->isRemote()) {
            return 0;
        }

        return $zoneFeeKopecks;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function deliveryBenefit(): ?array
    {
        return $this->promotionPolicies->find()?->deliveryBenefit();
    }
}
