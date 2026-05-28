<?php

namespace App\Application\Shopping\Delivery;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\Delivery\DeliveryPricingPolicy;
use App\Domain\Shopping\Delivery\DeliveryPricingResult;
use App\Domain\SystemContent\Ports\CompanyDeliveryTermsPort;

final class ResolveDeliveryPricing
{
    public function __construct(
        private readonly DeliveryPricingPolicy $policy,
        private readonly CompanyDeliveryTermsPort $companyTerms,
    ) {}

    public function fromCartState(CartState $cartState, ?string $deliveryMethod): DeliveryPricingResult
    {
        $method = $this->parseMethod($deliveryMethod);
        $terms = $this->companyTerms->current();

        return $this->policy->calculate(
            method: $method,
            itemsPayableKopecks: $cartState->subtotalUserKopecks,
            itemsTotalKopecks: $cartState->grandTotalKopecks,
            thresholdKopecks: $terms->freeDeliveryThresholdKopecks,
            configuredFeeKopecks: $terms->deliveryFeeKopecks,
        );
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, final_price_kopecks?: int}>  $placementRows
     */
    public function fromPlacementRows(array $placementRows, ?string $deliveryMethod): DeliveryPricingResult
    {
        $sum = 0;
        foreach ($placementRows as $row) {
            $qty = (int) ($row['quantity'] ?? 0);
            $unit = (int) ($row['final_price_kopecks'] ?? 0);
            $sum += max(0, $unit) * max(0, $qty);
        }

        $method = $this->parseMethod($deliveryMethod);
        $terms = $this->companyTerms->current();

        return $this->policy->calculate(
            method: $method,
            itemsPayableKopecks: $sum,
            itemsTotalKopecks: $sum,
            thresholdKopecks: $terms->freeDeliveryThresholdKopecks,
            configuredFeeKopecks: $terms->deliveryFeeKopecks,
        );
    }

    private function parseMethod(?string $deliveryMethod): ?DeliveryMethod
    {
        if ($deliveryMethod === null || $deliveryMethod === '') {
            return null;
        }

        return DeliveryMethod::tryFrom($deliveryMethod);
    }
}
