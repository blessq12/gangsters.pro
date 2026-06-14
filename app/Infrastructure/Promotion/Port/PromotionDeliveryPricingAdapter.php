<?php

namespace App\Infrastructure\Promotion\Port;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
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

        $deliveryConfiguration = $this->deliveryConfigurations->findPublic();
        $baseInZoneFee = $deliveryConfiguration?->deliveryFeeKopecks() ?? 0;
        $baseOutsideZoneFee = $deliveryConfiguration?->outsideZoneDeliveryFeeKopecks() ?? $baseInZoneFee;

        $policy = $promotionPolicy?->deliveryBenefitPolicy();
        if ($policy === null || ! $policy->isActive()) {
            return $inZone === false ? $baseOutsideZoneFee : $baseInZoneFee;
        }

        $thresholdKopecks = $policy->freeDeliveryThresholdKopecks();

        if ($currentKopecks < $thresholdKopecks) {
            return $inZone === false ? $baseOutsideZoneFee : $baseInZoneFee;
        }

        if (
            $inZone === false
            && $policy->outsideZoneAtThresholdFeeMode() === DeliveryFeeMode::BasePlusSurcharge
        ) {
            return $baseInZoneFee + $policy->outsideZoneSurchargeKopecks();
        }

        if ($policy->inZoneAtThresholdFeeMode() === DeliveryFeeMode::Free) {
            return 0;
        }

        return $baseInZoneFee;
    }
}
