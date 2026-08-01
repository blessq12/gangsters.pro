<?php

namespace App\Infrastructure\Order\Mapper;

use App\Domain\Order\Entity\PromotionPolicy;
use App\Infrastructure\Order\Model\PRM_Configuration;

final class PromotionPolicyMapper
{
    public function toDomain(PRM_Configuration $row): PromotionPolicy
    {
        $giftActive = (bool) $row->gift_benefit_active;

        return new PromotionPolicy(
            id: (int) $row->id,
            giftRules: [
                [
                    'order_channel' => 'pickup',
                    'min_order_amount_kopecks' => $this->requiredPositiveInt(
                        $row->gift_pickup_min_order_kopecks,
                        'gift_pickup_min_order_kopecks',
                    ),
                    'benefit_type' => 'free_roll_gift',
                    'is_active' => $giftActive,
                ],
                [
                    'order_channel' => 'courier',
                    'min_order_amount_kopecks' => $this->requiredPositiveInt(
                        $row->gift_courier_min_order_kopecks,
                        'gift_courier_min_order_kopecks',
                    ),
                    'benefit_type' => 'free_roll_gift',
                    'is_active' => $giftActive,
                ],
            ],
            deliveryBenefit: [
                'free_delivery_threshold_kopecks' => $this->requiredNonNegativeInt(
                    $row->delivery_free_threshold_kopecks,
                    'delivery_free_threshold_kopecks',
                ),
                'outside_zone_surcharge_kopecks' => $this->requiredNonNegativeInt(
                    $row->delivery_outside_zone_surcharge_kopecks,
                    'delivery_outside_zone_surcharge_kopecks',
                ),
                'below_threshold_fee_mode' => 'base_tariff',
                'in_zone_at_threshold_fee_mode' => 'free',
                'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
                'is_active' => (bool) $row->delivery_benefit_active,
            ],
            complementSetBenefit: [
                'rolls_per_set' => $this->requiredPositiveInt(
                    $row->complement_set_rolls_per_set,
                    'complement_set_rolls_per_set',
                ),
                'is_active' => (bool) $row->complement_set_benefit_active,
            ],
        );
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
