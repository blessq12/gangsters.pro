<?php

namespace App\Application\Shopping\Delivery;

use App\Domain\Shopping\Delivery\DeliveryPricingResult;
use App\Support\Money;

final class DeliveryPricingPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(DeliveryPricingResult $pricing): array
    {
        return [
            'method' => $pricing->effectiveMethod->value,
            'items_payable_kopecks' => $pricing->itemsPayableKopecks,
            'items_payable_rub' => Money::kopecksToApiRubles($pricing->itemsPayableKopecks),
            'free_delivery_threshold_kopecks' => $pricing->freeDeliveryThresholdKopecks,
            'free_delivery_threshold_rub' => $pricing->freeDeliveryThresholdKopecks !== null
                ? Money::kopecksToApiRubles($pricing->freeDeliveryThresholdKopecks)
                : null,
            'configured_delivery_fee_kopecks' => $pricing->configuredDeliveryFeeKopecks,
            'configured_delivery_fee_rub' => $pricing->configuredDeliveryFeeKopecks !== null
                ? Money::kopecksToApiRubles($pricing->configuredDeliveryFeeKopecks)
                : null,
            'delivery_fee_kopecks' => $pricing->deliveryFeeKopecks,
            'delivery_fee_rub' => Money::kopecksToApiRubles($pricing->deliveryFeeKopecks),
            'is_free' => $pricing->isFree(),
            'remaining_to_free_kopecks' => $pricing->remainingToFreeKopecks,
            'remaining_to_free_rub' => Money::kopecksToApiRubles($pricing->remainingToFreeKopecks),
            'items_total_kopecks' => $pricing->itemsTotalKopecks,
            'items_total_rub' => Money::kopecksToApiRubles($pricing->itemsTotalKopecks),
            'grand_total_kopecks' => $pricing->grandTotalKopecks,
            'grand_total_rub' => Money::kopecksToApiRubles($pricing->grandTotalKopecks),
        ];
    }
}
