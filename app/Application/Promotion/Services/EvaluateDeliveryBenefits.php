<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Port\PromotionDeliveryPricingPort;
use App\Shared\Enum\DeliveryMethod;

/**
 * Расчёт delivery-бенефита и delivery_pricing.
 */
final class EvaluateDeliveryBenefits
{
    public function __construct(
        private readonly PromotionDeliveryPricingPort $deliveryPricing,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildBenefit(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
    ): array {
        if ($deliveryMethod === DeliveryMethod::Pickup) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $thresholdKopecks = $this->deliveryPricing->resolveFreeDeliveryThresholdKopecks();
        if ($thresholdKopecks === null) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $isReached = $currentKopecks >= $thresholdKopecks;
        $remainingKopecks = $isReached
            ? 0
            : max(0, $thresholdKopecks - $currentKopecks);

        $benefit = [
            'isActive' => true,
            'isReached' => $isReached,
            'thresholdKopecks' => $thresholdKopecks,
            'currentKopecks' => $currentKopecks,
            'remainingKopecks' => $remainingKopecks,
        ];

        if ($deliveryMethod === null) {
            $benefit['isPreview'] = true;
        }

        return $benefit;
    }

    public function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        ?float $deliveryLatitude,
        ?float $deliveryLongitude,
    ): int {
        $inZone = $this->deliveryPricing->resolveInZone($deliveryLatitude, $deliveryLongitude);

        return $this->deliveryPricing->resolveDeliveryFeeKopecks(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $deliveryMethod,
            currentKopecks: $currentKopecks,
            inZone: $inZone,
        );
    }

    public function resolveInZone(?float $deliveryLatitude, ?float $deliveryLongitude): ?bool
    {
        return $this->deliveryPricing->resolveInZone($deliveryLatitude, $deliveryLongitude);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildPricing(
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        int $deliveryFeeKopecks,
        ?PromotionPolicy $promotionPolicy,
        ?bool $inZone = null,
    ): ?array {
        $isPreview = $deliveryMethod === null;
        $effectiveMethod = $deliveryMethod ?? DeliveryMethod::Courier;

        if (! $isPreview && $effectiveMethod === DeliveryMethod::Pickup) {
            $method = $effectiveMethod->value;
            $grandTotalKopecks = $currentKopecks;

            return [
                'method' => $method,
                'items_payable_kopecks' => $currentKopecks,
                'delivery_fee_kopecks' => 0,
                'is_free' => true,
                'remaining_to_free_kopecks' => 0,
                'items_total_kopecks' => $currentKopecks,
                'grand_total_kopecks' => $grandTotalKopecks,
                'items_total_rub' => $currentKopecks / 100,
                'delivery_fee_rub' => 0,
                'grand_total_rub' => $grandTotalKopecks / 100,
                'is_preview' => false,
                'in_zone' => null,
            ];
        }

        $method = $effectiveMethod->value;
        $grandTotalKopecks = $currentKopecks + $deliveryFeeKopecks;
        $freeThresholdKopecks = $this->deliveryPricing->resolveFreeDeliveryThresholdKopecks();
        $remainingToFreeKopecks = 0;

        if (
            $effectiveMethod === DeliveryMethod::Courier
            && $freeThresholdKopecks !== null
            && $currentKopecks < $freeThresholdKopecks
        ) {
            $remainingToFreeKopecks = $freeThresholdKopecks - $currentKopecks;
        }

        $baseDeliveryFeeKopecks = $deliveryFeeKopecks;
        $outsideZoneSurchargeKopecks = 0;

        if (! $isPreview && $effectiveMethod === DeliveryMethod::Courier && $inZone === false) {
            $inZoneDeliveryFeeKopecks = $this->deliveryPricing->resolveDeliveryFeeKopecks(
                promotionPolicy: $promotionPolicy,
                deliveryMethod: $deliveryMethod,
                currentKopecks: $currentKopecks,
                inZone: true,
            );
            $baseDeliveryFeeKopecks = $inZoneDeliveryFeeKopecks;
            $outsideZoneSurchargeKopecks = max(0, $deliveryFeeKopecks - $inZoneDeliveryFeeKopecks);
        }

        return [
            'method' => $method,
            'items_payable_kopecks' => $currentKopecks,
            'delivery_fee_kopecks' => $deliveryFeeKopecks,
            'base_delivery_fee_kopecks' => $baseDeliveryFeeKopecks,
            'outside_zone_surcharge_kopecks' => $outsideZoneSurchargeKopecks,
            'is_free' => $effectiveMethod === DeliveryMethod::Courier && $deliveryFeeKopecks === 0,
            'remaining_to_free_kopecks' => max(0, $remainingToFreeKopecks),
            'items_total_kopecks' => $currentKopecks,
            'grand_total_kopecks' => $grandTotalKopecks,
            'items_total_rub' => $currentKopecks / 100,
            'delivery_fee_rub' => $deliveryFeeKopecks / 100,
            'base_delivery_fee_rub' => $baseDeliveryFeeKopecks / 100,
            'outside_zone_surcharge_rub' => $outsideZoneSurchargeKopecks / 100,
            'grand_total_rub' => $grandTotalKopecks / 100,
            'is_preview' => $isPreview,
            'in_zone' => $inZone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inactiveBenefit(int $currentKopecks): array
    {
        return [
            'isActive' => false,
            'isReached' => false,
            'thresholdKopecks' => null,
            'currentKopecks' => $currentKopecks,
            'remainingKopecks' => 0,
        ];
    }
}
