<?php

namespace App\Application\Order\Query;

use App\Domain\Order\Repository\PromotionPolicyRepository;

final class GetPromotionPolicyFormUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $policy = $this->policies->find();
        if ($policy === null) {
            throw new \InvalidArgumentException('Правила акций не найдены.');
        }

        $pickup = $policy->giftRuleForChannel('pickup');
        $courier = $policy->giftRuleForChannel('courier');
        $delivery = $policy->deliveryBenefit();
        $complement = $policy->complementSetBenefit();

        return [
            'gift_pickup_min_order_rubles' => intdiv((int) ($pickup['min_order_amount_kopecks'] ?? 0), 100),
            'gift_courier_min_order_rubles' => intdiv((int) ($courier['min_order_amount_kopecks'] ?? 0), 100),
            'gift_courier_weekdays' => array_values($courier['allowed_weekdays'] ?? []),
            'gift_benefit_active' => (bool) ($pickup['is_active'] ?? false),
            'delivery_free_threshold_rubles' => intdiv((int) $delivery['free_delivery_threshold_kopecks'], 100),
            'delivery_outside_zone_surcharge_rubles' => intdiv((int) $delivery['outside_zone_surcharge_kopecks'], 100),
            'delivery_benefit_active' => (bool) $delivery['is_active'],
            'complement_set_benefit_active' => (bool) $complement['is_active'],
            'complement_set_rolls_per_set' => (int) $complement['rolls_per_set'],
            'complement_set_max_sets' => (int) $complement['max_sets'],
        ];
    }
}
