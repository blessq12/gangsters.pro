<?php

namespace App\Domain\Order\Entity;

use Carbon\Carbon;

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
     *     is_active: bool,
     *     allowed_weekdays: list<int>
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
     *     is_active: bool,
     *     allowed_weekdays: list<int>
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
     *     is_active: bool,
     *     allowed_weekdays: list<int>
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

    /**
     * Пустой allowed_weekdays = без ограничения по дням.
     * Дни: ISO-8601 (1 = пн … 7 = вс).
     *
     * @param  array{
     *     order_channel?: string,
     *     min_order_amount_kopecks?: int,
     *     benefit_type?: string,
     *     is_active?: bool,
     *     allowed_weekdays?: list<int>
     * }  $rule
     */
    public function isGiftRuleAllowedOnDate(array $rule, \DateTimeInterface $at, string $timezone): bool
    {
        if (! ($rule['is_active'] ?? false)) {
            return false;
        }

        $days = $rule['allowed_weekdays'] ?? [];
        if (! is_array($days) || $days === []) {
            return true;
        }

        $normalized = [];
        foreach ($days as $day) {
            $int = (int) $day;
            if ($int >= 1 && $int <= 7) {
                $normalized[$int] = $int;
            }
        }

        if ($normalized === []) {
            return true;
        }

        $isoWeekday = Carbon::parse($at)->timezone($timezone)->isoWeekday();

        return isset($normalized[$isoWeekday]);
    }
}
