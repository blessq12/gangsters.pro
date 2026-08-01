<?php

namespace App\Domain\Order\Entity;

/**
 * Singleton-конфигурация коммерческих правил.
 */
final class PromotionPolicy
{
    /**
     * @param  list<array{
     *     order_channel: string,
     *     min_order_amount_kopecks: int,
     *     benefit_type: string,
     *     is_active: bool
     * }>  $giftRules
     * @param  array{
     *     free_delivery_threshold_kopecks: int,
     *     outside_zone_surcharge_kopecks: int,
     *     below_threshold_fee_mode: string,
     *     in_zone_at_threshold_fee_mode: string,
     *     outside_zone_at_threshold_fee_mode: string,
     *     is_active: bool
     * }  $deliveryBenefit
     * @param  array{rolls_per_set: int, is_active: bool}  $complementSetBenefit
     */
    public function __construct(
        private readonly int $id,
        private readonly array $giftRules,
        private readonly array $deliveryBenefit,
        private readonly array $complementSetBenefit,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return list<array{
     *     order_channel: string,
     *     min_order_amount_kopecks: int,
     *     benefit_type: string,
     *     is_active: bool
     * }>
     */
    public function giftRules(): array
    {
        return $this->giftRules;
    }

    /**
     * @return array{
     *     free_delivery_threshold_kopecks: int,
     *     outside_zone_surcharge_kopecks: int,
     *     below_threshold_fee_mode: string,
     *     in_zone_at_threshold_fee_mode: string,
     *     outside_zone_at_threshold_fee_mode: string,
     *     is_active: bool
     * }
     */
    public function deliveryBenefit(): array
    {
        return $this->deliveryBenefit;
    }

    /**
     * @return array{rolls_per_set: int, is_active: bool}
     */
    public function complementSetBenefit(): array
    {
        return $this->complementSetBenefit;
    }

    /**
     * @return array{
     *     order_channel: string,
     *     min_order_amount_kopecks: int,
     *     benefit_type: string,
     *     is_active: bool
     * }|null
     */
    public function giftRuleForChannel(string $channel): ?array
    {
        foreach ($this->giftRules as $rule) {
            if (($rule['order_channel'] ?? null) === $channel) {
                return $rule;
            }
        }

        return null;
    }
}
