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

        $policy = $promotionPolicy?->deliveryBenefitPolicy();
        if ($policy === null || ! $policy->isActive()) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $thresholdKopecks = $policy->freeDeliveryThresholdKopecks();
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

    /**
     * @return array<string, mixed>|null
     */
    public function buildPricing(
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        int $deliveryFeeKopecks,
        ?PromotionPolicy $promotionPolicy,
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
            ];
        }

        $method = $effectiveMethod->value;
        $grandTotalKopecks = $currentKopecks + $deliveryFeeKopecks;
        $freeThresholdKopecks = $promotionPolicy?->deliveryBenefitPolicy()->freeDeliveryThresholdKopecks();
        $remainingToFreeKopecks = 0;

        if (
            $effectiveMethod === DeliveryMethod::Courier
            && is_int($freeThresholdKopecks)
            && $currentKopecks < $freeThresholdKopecks
        ) {
            $remainingToFreeKopecks = $freeThresholdKopecks - $currentKopecks;
        }

        return [
            'method' => $method,
            'items_payable_kopecks' => $currentKopecks,
            'delivery_fee_kopecks' => $deliveryFeeKopecks,
            'is_free' => $effectiveMethod === DeliveryMethod::Courier && $deliveryFeeKopecks === 0,
            'remaining_to_free_kopecks' => max(0, $remainingToFreeKopecks),
            'items_total_kopecks' => $currentKopecks,
            'grand_total_kopecks' => $grandTotalKopecks,
            'items_total_rub' => $currentKopecks / 100,
            'delivery_fee_rub' => $deliveryFeeKopecks / 100,
            'grand_total_rub' => $grandTotalKopecks / 100,
            'is_preview' => $isPreview,
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
