<?php

namespace App\Infrastructure\Promotion\Port;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Port\PromotionDeliveryPricingPort;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Geo\PointInGeoJsonZone;

final class PromotionDeliveryPricingAdapter implements PromotionDeliveryPricingPort
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
    ) {}

    public function resolveInZone(?float $latitude, ?float $longitude): ?bool
    {
        $configuration = $this->deliveryConfigurations->findPublic();

        if (! $configuration instanceof DeliveryConfiguration) {
            return null;
        }

        return PointInGeoJsonZone::contains(
            $configuration->deliveryZoneGeoJson(),
            $latitude,
            $longitude,
        );
    }

    public function resolveFreeDeliveryThresholdKopecks(): ?int
    {
        return $this->deliveryConfigurations->findPublic()?->minOrderAmountKopecks();
    }

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        ?bool $inZone,
    ): int {
        if ($deliveryMethod === DeliveryMethod::Pickup) {
            return 0;
        }

        if ($deliveryMethod !== DeliveryMethod::Courier) {
            return 0;
        }

        $configuration = $this->deliveryConfigurations->findPublic();
        $baseFeeKopecks = max(0, $configuration?->deliveryFeeKopecks() ?? 0);
        $outsideZoneFeeKopecks = max(
            0,
            $configuration?->outsideZoneDeliveryFeeKopecks() ?? $baseFeeKopecks,
        );
        $minOrderAmountKopecks = $configuration?->minOrderAmountKopecks();
        $meetsMinOrderAmount = $minOrderAmountKopecks !== null
            && $currentKopecks >= $minOrderAmountKopecks;

        if ($inZone === false) {
            if ($meetsMinOrderAmount) {
                return $outsideZoneFeeKopecks;
            }

            return $baseFeeKopecks + $outsideZoneFeeKopecks;
        }

        if ($meetsMinOrderAmount) {
            return 0;
        }

        return $baseFeeKopecks;
    }
}
