<?php

namespace App\Infrastructure\Promotion\Mapper;

use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Infrastructure\Promotion\Model\PRM_Policy;

final class PromotionPolicyMapper
{
    /** @var list<int> */
    private const DEFAULT_COURIER_WEEKDAYS = [1, 2, 3, 4];

    public function toDomain(PRM_Policy $row): PromotionPolicy
    {
        $giftRules = is_array($row->gift_rules) ? $row->gift_rules : [];
        $deliveryBenefit = is_array($row->delivery_benefit) ? $row->delivery_benefit : [];
        $complementSetBenefit = is_array($row->complement_set_benefit) ? $row->complement_set_benefit : [];

        return new PromotionPolicy(
            id: (int) $row->id,
            branchId: (int) $row->branch_id,
            giftRules: $giftRules,
            deliveryBenefit: $deliveryBenefit,
            complementSetBenefit: $complementSetBenefit,
        );
    }

    /**
     * @param  array{
     *     gift_pickup_min_order_kopecks: int,
     *     gift_courier_min_order_kopecks: int,
     *     gift_courier_weekdays: list<int>,
     *     gift_benefit_active: bool,
     *     delivery_free_threshold_kopecks: int,
     *     delivery_outside_zone_surcharge_kopecks: int,
     *     delivery_benefit_active: bool,
     *     complement_set_benefit_active: bool,
     *     complement_set_rolls_per_set: int,
     *     complement_set_max_sets: int
     * }  $fields
     * @return array{gift_rules: list<array<string, mixed>>, delivery_benefit: array<string, mixed>, complement_set_benefit: array<string, mixed>}
     */
    public function flatFieldsToPersistence(array $fields): array
    {
        $giftActive = (bool) $fields['gift_benefit_active'];

        return [
            'gift_rules' => [
                [
                    'order_channel' => 'pickup',
                    'min_order_amount_kopecks' => $this->requiredPositiveInt(
                        $fields['gift_pickup_min_order_kopecks'],
                        'gift_pickup_min_order_kopecks',
                    ),
                    'benefit_type' => 'free_roll_gift',
                    'is_active' => $giftActive,
                    'allowed_weekdays' => [],
                ],
                [
                    'order_channel' => 'courier',
                    'min_order_amount_kopecks' => $this->requiredPositiveInt(
                        $fields['gift_courier_min_order_kopecks'],
                        'gift_courier_min_order_kopecks',
                    ),
                    'benefit_type' => 'free_roll_gift',
                    'is_active' => $giftActive,
                    'allowed_weekdays' => $this->normalizeWeekdays(
                        $fields['gift_courier_weekdays'],
                        self::DEFAULT_COURIER_WEEKDAYS,
                    ),
                ],
            ],
            'delivery_benefit' => [
                'free_delivery_threshold_kopecks' => $this->requiredNonNegativeInt(
                    $fields['delivery_free_threshold_kopecks'],
                    'delivery_free_threshold_kopecks',
                ),
                'outside_zone_surcharge_kopecks' => $this->requiredNonNegativeInt(
                    $fields['delivery_outside_zone_surcharge_kopecks'],
                    'delivery_outside_zone_surcharge_kopecks',
                ),
                'below_threshold_fee_mode' => 'base_tariff',
                'in_zone_at_threshold_fee_mode' => 'free',
                'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
                'is_active' => (bool) $fields['delivery_benefit_active'],
            ],
            'complement_set_benefit' => [
                'rolls_per_set' => $this->requiredPositiveInt(
                    $fields['complement_set_rolls_per_set'],
                    'complement_set_rolls_per_set',
                ),
                'max_sets' => $this->requiredPositiveInt(
                    $fields['complement_set_max_sets'],
                    'complement_set_max_sets',
                ),
                'is_active' => (bool) $fields['complement_set_benefit_active'],
            ],
        ];
    }

    /**
     * @param  mixed  $raw
     * @param  list<int>  $fallback
     * @return list<int>
     */
    private function normalizeWeekdays(mixed $raw, array $fallback): array
    {
        if (! is_array($raw) || $raw === []) {
            return $fallback;
        }

        $days = [];
        foreach ($raw as $day) {
            $int = (int) $day;
            if ($int >= 1 && $int <= 7) {
                $days[$int] = $int;
            }
        }

        if ($days === []) {
            return $fallback;
        }

        $list = array_values($days);
        sort($list);

        return $list;
    }

    private function requiredPositiveInt(mixed $value, string $field): int
    {
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException("Поле {$field} обязательно для политики акций.");
        }

        $int = (int) $value;
        if ($int <= 0) {
            throw new \InvalidArgumentException("Поле {$field} должно быть положительным.");
        }

        return $int;
    }

    private function requiredNonNegativeInt(mixed $value, string $field): int
    {
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException("Поле {$field} обязательно для политики акций.");
        }

        $int = (int) $value;
        if ($int < 0) {
            throw new \InvalidArgumentException("Поле {$field} не может быть отрицательным.");
        }

        return $int;
    }
}
