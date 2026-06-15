<?php

namespace App\Infrastructure\Promotion\Port;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
use App\Domain\Promotion\Port\PromotionDeliveryPricingPort;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\DeliveryBenefitPolicy;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Geo\PointInGeoJsonZone;

final class PromotionDeliveryPricingAdapter implements PromotionDeliveryPricingPort
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
        private readonly PromotionPolicyRepository $promotionPolicies,
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
        $policy = $this->deliveryBenefitPolicy();

        if ($policy === null || ! $policy->isActive()) {
            return null;
        }

        return $policy->freeDeliveryThresholdKopecks();
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

        $policy = $promotionPolicy?->deliveryBenefitPolicy();
        if (! $policy instanceof DeliveryBenefitPolicy || ! $policy->isActive()) {
            if ($inZone === false) {
                return $baseFeeKopecks + $outsideZoneFeeKopecks;
            }

            return $baseFeeKopecks;
        }

        $thresholdKopecks = $policy->freeDeliveryThresholdKopecks();
        $meetsThreshold = $currentKopecks >= $thresholdKopecks;

        if ($inZone === false) {
            if ($meetsThreshold) {
                return $this->resolveFeeByMode(
                    $policy->outsideZoneAtThresholdFeeMode(),
                    $baseFeeKopecks,
                    $policy->outsideZoneSurchargeKopecks(),
                );
            }

            return $baseFeeKopecks + $outsideZoneFeeKopecks;
        }

        if ($meetsThreshold) {
            return $this->resolveFeeByMode(
                $policy->inZoneAtThresholdFeeMode(),
                $baseFeeKopecks,
                $policy->outsideZoneSurchargeKopecks(),
            );
        }

        return $this->resolveFeeByMode(
            $policy->belowThresholdFeeMode(),
            $baseFeeKopecks,
            $policy->outsideZoneSurchargeKopecks(),
        );
    }

    private function deliveryBenefitPolicy(): ?DeliveryBenefitPolicy
    {
        return $this->promotionPolicies->find()?->deliveryBenefitPolicy();
    }

    private function resolveFeeByMode(
        DeliveryFeeMode $mode,
        int $baseFeeKopecks,
        int $outsideZoneSurchargeKopecks,
    ): int {
        return match ($mode) {
            DeliveryFeeMode::Free => 0,
            DeliveryFeeMode::BasePlusSurcharge => $baseFeeKopecks + $outsideZoneSurchargeKopecks,
            DeliveryFeeMode::BaseTariff => $baseFeeKopecks,
        };
    }
}
