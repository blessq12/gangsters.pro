<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Order\Entity\PromotionPolicy;
use App\Domain\Order\Port\PromotionDeliveryPricingPort;
use App\Domain\Order\Repository\PromotionPolicyRepository;
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
        $policy = $this->deliveryBenefit();

        if ($policy === null || ! ($policy['is_active'] ?? false)) {
            return null;
        }

        return (int) $policy['free_delivery_threshold_kopecks'];
    }

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?string $deliveryMethod,
        int $currentKopecks,
        ?bool $inZone,
    ): int {
        if ($deliveryMethod === 'pickup' || $deliveryMethod !== 'courier') {
            return 0;
        }

        $configuration = $this->deliveryConfigurations->findPublic();
        $baseFeeKopecks = max(0, $configuration?->deliveryFeeKopecks() ?? 0);
        $outsideZoneFeeKopecks = max(
            0,
            $configuration?->outsideZoneDeliveryFeeKopecks() ?? $baseFeeKopecks,
        );

        $policy = $promotionPolicy?->deliveryBenefit();
        if (! is_array($policy) || ! ($policy['is_active'] ?? false)) {
            if ($inZone === false) {
                return $baseFeeKopecks + $outsideZoneFeeKopecks;
            }

            return $baseFeeKopecks;
        }

        $thresholdKopecks = (int) $policy['free_delivery_threshold_kopecks'];
        $meetsThreshold = $currentKopecks >= $thresholdKopecks;
        $surcharge = (int) $policy['outside_zone_surcharge_kopecks'];

        if ($inZone === false) {
            if ($meetsThreshold) {
                return $this->resolveFeeByMode(
                    (string) $policy['outside_zone_at_threshold_fee_mode'],
                    $baseFeeKopecks,
                    $surcharge,
                );
            }

            return $baseFeeKopecks + $outsideZoneFeeKopecks;
        }

        if ($meetsThreshold) {
            return $this->resolveFeeByMode(
                (string) $policy['in_zone_at_threshold_fee_mode'],
                $baseFeeKopecks,
                $surcharge,
            );
        }

        return $this->resolveFeeByMode(
            (string) $policy['below_threshold_fee_mode'],
            $baseFeeKopecks,
            $surcharge,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function deliveryBenefit(): ?array
    {
        return $this->promotionPolicies->find()?->deliveryBenefit();
    }

    private function resolveFeeByMode(
        string $mode,
        int $baseFeeKopecks,
        int $outsideZoneSurchargeKopecks,
    ): int {
        return match ($mode) {
            'free' => 0,
            'base_plus_surcharge' => $baseFeeKopecks + $outsideZoneSurchargeKopecks,
            'outside_zone_surcharge_only' => $outsideZoneSurchargeKopecks,
            default => $baseFeeKopecks,
        };
    }
}
