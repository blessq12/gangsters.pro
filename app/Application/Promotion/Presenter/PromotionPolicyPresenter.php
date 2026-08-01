<?php

namespace App\Application\Promotion\Presenter;

use App\Domain\Promotion\Entity\PromotionPolicy;

final class PromotionPolicyPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(PromotionPolicy $policy): array
    {
        $pickupRule = $policy->giftRuleForChannel('pickup');
        $courierRule = $policy->giftRuleForChannel('courier');
        $delivery = $policy->deliveryBenefit();
        $complement = $policy->complementSetBenefit();

        return [
            'gift' => [
                'active' => $this->giftRuleActive($pickupRule) || $this->giftRuleActive($courierRule),
                'pickup_min_kopecks' => $pickupRule['min_order_amount_kopecks'] ?? null,
                'courier_min_kopecks' => $courierRule['min_order_amount_kopecks'] ?? null,
            ],
            'complement' => [
                'active' => (bool) ($complement['is_active'] ?? false),
                'rolls_per_set' => $complement['rolls_per_set'] ?? null,
            ],
            'delivery_benefit' => [
                'active' => (bool) ($delivery['is_active'] ?? false),
                'free_threshold_kopecks' => $delivery['free_delivery_threshold_kopecks'] ?? null,
                'outside_zone_surcharge_kopecks' => $delivery['outside_zone_surcharge_kopecks'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $rule
     */
    private function giftRuleActive(?array $rule): bool
    {
        return is_array($rule) && (bool) ($rule['is_active'] ?? false);
    }
}
