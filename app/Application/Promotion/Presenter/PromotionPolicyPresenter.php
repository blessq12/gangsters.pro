<?php

namespace App\Application\Promotion\Presenter;

use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;
use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Policy\PromotionPolicy;

final class PromotionPolicyPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function publicPolicy(PromotionPolicy $policy): array
    {
        $pickup = $policy->giftRuleForChannel('pickup');
        $courier = $policy->giftRuleForChannel('courier');
        $delivery = $policy->deliveryBenefit();
        $complement = $policy->complementSetBenefit();

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

    /**
     * @return array<string, mixed>
     */
    public function adminPolicy(PromotionPolicy $policy): array
    {
        return $this->publicPolicy($policy);
    }

    /**
     * @return array<string, mixed>
     */
    public function role(ProductPromotionRole $role): array
    {
        return [
            'product_id' => $role->productId(),
            'role' => $role->role()->value,
            'name' => $role->name(),
            'price_kopecks' => $role->priceKopecks(),
            'paid_twin_product_id' => $role->paidTwinProductId(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function zone(PromotionZone $zone): array
    {
        return [
            'id' => $zone->id(),
            'name' => $zone->name(),
            'fee_kopecks' => $zone->feeKopecks(),
            'is_remote' => $zone->isRemote(),
        ];
    }
}
