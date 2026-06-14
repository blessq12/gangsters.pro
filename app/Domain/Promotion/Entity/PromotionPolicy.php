<?php

namespace App\Domain\Promotion\Entity;

use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;
use App\Domain\Promotion\ValueObject\DeliveryBenefitPolicy;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;

/**
 * Singleton-конфигурация коммерческих правил (подарок, доставка, комплект дополнений).
 */
final class PromotionPolicy
{
    /**
     * @param  list<GiftBenefitRule>  $giftRules
     */
    public function __construct(
        private readonly int $id,
        private readonly array $giftRules,
        private readonly DeliveryBenefitPolicy $deliveryBenefitPolicy,
        private readonly ComplementSetBenefitRule $complementSetBenefitRule,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return list<GiftBenefitRule>
     */
    public function giftRules(): array
    {
        return $this->giftRules;
    }

    public function deliveryBenefitPolicy(): DeliveryBenefitPolicy
    {
        return $this->deliveryBenefitPolicy;
    }

    public function complementSetBenefitRule(): ComplementSetBenefitRule
    {
        return $this->complementSetBenefitRule;
    }

    public function giftRuleForChannel(PromotionOrderChannel $channel): ?GiftBenefitRule
    {
        foreach ($this->giftRules as $rule) {
            if ($rule->orderChannel() === $channel) {
                return $rule;
            }
        }

        return null;
    }
}
