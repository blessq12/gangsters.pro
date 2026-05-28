<?php

namespace App\Domain\Shopping\Delivery;

use App\Domain\Order\Enums\DeliveryMethod;

/**
 * Чистая политика платы за доставку от суммы пользовательских строк корзины.
 */
final class DeliveryPricingPolicy
{
    public function calculate(
        ?DeliveryMethod $method,
        int $itemsPayableKopecks,
        int $itemsTotalKopecks,
        ?int $thresholdKopecks,
        ?int $configuredFeeKopecks,
    ): DeliveryPricingResult {
        $effectiveMethod = $method ?? DeliveryMethod::Courier;
        $itemsPayableKopecks = max(0, $itemsPayableKopecks);
        $itemsTotalKopecks = max(0, $itemsTotalKopecks);

        $snapshotThreshold = $this->snapshotThreshold($thresholdKopecks, $configuredFeeKopecks);
        $snapshotFee = $this->snapshotConfiguredFee($thresholdKopecks, $configuredFeeKopecks);

        $fee = $this->resolveFee(
            $effectiveMethod,
            $itemsPayableKopecks,
            $thresholdKopecks,
            $configuredFeeKopecks,
        );

        $remaining = $this->remainingToFree(
            $effectiveMethod,
            $itemsPayableKopecks,
            $thresholdKopecks,
            $configuredFeeKopecks,
        );

        return new DeliveryPricingResult(
            effectiveMethod: $effectiveMethod,
            itemsPayableKopecks: $itemsPayableKopecks,
            freeDeliveryThresholdKopecks: $snapshotThreshold,
            configuredDeliveryFeeKopecks: $snapshotFee,
            deliveryFeeKopecks: $fee,
            remainingToFreeKopecks: $remaining,
            itemsTotalKopecks: $itemsTotalKopecks,
            grandTotalKopecks: $itemsTotalKopecks + $fee,
        );
    }

    private function resolveFee(
        DeliveryMethod $method,
        int $itemsPayableKopecks,
        ?int $thresholdKopecks,
        ?int $configuredFeeKopecks,
    ): int {
        if ($method === DeliveryMethod::Pickup) {
            return 0;
        }

        if (! $this->hasActivePaidDeliveryConfig($thresholdKopecks, $configuredFeeKopecks)) {
            return 0;
        }

        $threshold = (int) $thresholdKopecks;
        $fee = max(0, (int) $configuredFeeKopecks);

        if ($itemsPayableKopecks >= $threshold) {
            return 0;
        }

        return $fee;
    }

    private function remainingToFree(
        DeliveryMethod $method,
        int $itemsPayableKopecks,
        ?int $thresholdKopecks,
        ?int $configuredFeeKopecks,
    ): int {
        if ($method === DeliveryMethod::Pickup) {
            return 0;
        }

        if (! $this->hasActivePaidDeliveryConfig($thresholdKopecks, $configuredFeeKopecks)) {
            return 0;
        }

        $threshold = (int) $thresholdKopecks;

        return max(0, $threshold - $itemsPayableKopecks);
    }

    private function hasActivePaidDeliveryConfig(?int $thresholdKopecks, ?int $configuredFeeKopecks): bool
    {
        if ($thresholdKopecks === null || $configuredFeeKopecks === null) {
            return false;
        }

        return (int) $thresholdKopecks > 0;
    }

    private function snapshotThreshold(?int $thresholdKopecks, ?int $configuredFeeKopecks): ?int
    {
        if (! $this->hasActivePaidDeliveryConfig($thresholdKopecks, $configuredFeeKopecks)) {
            return null;
        }

        return (int) $thresholdKopecks;
    }

    private function snapshotConfiguredFee(?int $thresholdKopecks, ?int $configuredFeeKopecks): ?int
    {
        if (! $this->hasActivePaidDeliveryConfig($thresholdKopecks, $configuredFeeKopecks)) {
            return null;
        }

        return max(0, (int) $configuredFeeKopecks);
    }
}
