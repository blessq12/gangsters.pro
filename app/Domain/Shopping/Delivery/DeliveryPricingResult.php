<?php

namespace App\Domain\Shopping\Delivery;

use App\Domain\Order\Enums\DeliveryMethod;

/**
 * Результат расчёта платы за доставку (копейки).
 */
final readonly class DeliveryPricingResult
{
    public function __construct(
        public DeliveryMethod $effectiveMethod,
        public int $itemsPayableKopecks,
        public ?int $freeDeliveryThresholdKopecks,
        public ?int $configuredDeliveryFeeKopecks,
        public int $deliveryFeeKopecks,
        public int $remainingToFreeKopecks,
        public int $itemsTotalKopecks,
        public int $grandTotalKopecks,
    ) {}

    public function isFree(): bool
    {
        return $this->deliveryFeeKopecks === 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSnapshotArray(): array
    {
        return [
            'method' => $this->effectiveMethod->value,
            'free_delivery_threshold_kopecks' => $this->freeDeliveryThresholdKopecks,
            'configured_delivery_fee_kopecks' => $this->configuredDeliveryFeeKopecks,
            'items_payable_kopecks' => $this->itemsPayableKopecks,
            'applied_delivery_fee_kopecks' => $this->deliveryFeeKopecks,
            'items_total_kopecks' => $this->itemsTotalKopecks,
            'grand_total_kopecks' => $this->grandTotalKopecks,
        ];
    }
}
