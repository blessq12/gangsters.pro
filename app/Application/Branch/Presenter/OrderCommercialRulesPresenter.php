<?php

namespace App\Application\Branch\Presenter;

use App\Domain\Core\Order\Entity\OrderCommercialRules;

final class OrderCommercialRulesPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(OrderCommercialRules $rules): array
    {
        $pickup = $rules->giftRuleForChannel('pickup');
        $courier = $rules->giftRuleForChannel('courier');
        $delivery = $rules->deliveryBenefit();
        $complement = $rules->complementSetBenefit();

        return [
            'gift' => [
                'is_active' => (bool) (($pickup['is_active'] ?? false) || ($courier['is_active'] ?? false)),
                'pickup_min_order_kopecks' => (int) ($pickup['min_order_amount_kopecks'] ?? 0),
                'courier_min_order_kopecks' => (int) ($courier['min_order_amount_kopecks'] ?? 0),
                'courier_weekdays' => array_values($courier['allowed_weekdays'] ?? []),
            ],
            'delivery' => [
                'is_active' => (bool) ($delivery['is_active'] ?? false),
                'free_delivery_threshold_kopecks' => (int) ($delivery['free_delivery_threshold_kopecks'] ?? 0),
            ],
            'complement' => [
                'is_active' => (bool) ($complement['is_active'] ?? false),
                'rolls_per_set' => (int) ($complement['rolls_per_set'] ?? 0),
                'max_sets' => (int) ($complement['max_sets'] ?? 0),
            ],
        ];
    }
}
