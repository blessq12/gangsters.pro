<?php

namespace App\Application\Promotion\Presenter;

use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;

final class PromotionPolicyPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(PromotionPolicy $policy): array
    {
        $pickupRule = $policy->giftRuleForChannel(PromotionOrderChannel::Pickup);
        $courierRule = $policy->giftRuleForChannel(PromotionOrderChannel::Courier);
        $deliveryPolicy = $policy->deliveryBenefitPolicy();
        $complementRule = $policy->complementSetBenefitRule();

        return [
            'gift' => [
                'active' => $this->giftRuleActive($pickupRule) || $this->giftRuleActive($courierRule),
                'pickup_min_kopecks' => $pickupRule?->minOrderAmountKopecks(),
                'courier_min_kopecks' => $courierRule?->minOrderAmountKopecks(),
            ],
            'complement' => [
                'active' => $complementRule->isActive(),
                'rolls_per_set' => $complementRule->rollsPerSet(),
            ],
            'delivery_benefit' => [
                'active' => $deliveryPolicy->isActive(),
                'free_threshold_kopecks' => $deliveryPolicy->freeDeliveryThresholdKopecks(),
                'outside_zone_surcharge_kopecks' => $deliveryPolicy->outsideZoneSurchargeKopecks(),
            ],
        ];
    }

    private function giftRuleActive(?GiftBenefitRule $rule): bool
    {
        return $rule instanceof GiftBenefitRule && $rule->isActive();
    }
}
