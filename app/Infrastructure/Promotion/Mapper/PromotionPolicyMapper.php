<?php

namespace App\Infrastructure\Promotion\Mapper;

use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
use App\Domain\Promotion\Enum\GiftBenefitType;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;
use App\Domain\Promotion\ValueObject\DeliveryBenefitPolicy;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;
use App\Infrastructure\Promotion\Model\PRM_Configuration;

final class PromotionPolicyMapper
{
    public function toDomain(PRM_Configuration $row): PromotionPolicy
    {
        $giftActive = (bool) $row->gift_benefit_active;

        return new PromotionPolicy(
            id: (int) $row->id,
            giftRules: [
                new GiftBenefitRule(
                    orderChannel: PromotionOrderChannel::Pickup,
                    minOrderAmountKopecks: $this->requiredPositiveInt(
                        $row->gift_pickup_min_order_kopecks,
                        'gift_pickup_min_order_kopecks',
                    ),
                    benefitType: GiftBenefitType::FreeRollGift,
                    isActive: $giftActive,
                ),
                new GiftBenefitRule(
                    orderChannel: PromotionOrderChannel::Courier,
                    minOrderAmountKopecks: $this->requiredPositiveInt(
                        $row->gift_courier_min_order_kopecks,
                        'gift_courier_min_order_kopecks',
                    ),
                    benefitType: GiftBenefitType::FreeRollGift,
                    isActive: $giftActive,
                ),
            ],
            deliveryBenefitPolicy: new DeliveryBenefitPolicy(
                freeDeliveryThresholdKopecks: $this->requiredNonNegativeInt(
                    $row->delivery_free_threshold_kopecks,
                    'delivery_free_threshold_kopecks',
                ),
                outsideZoneSurchargeKopecks: $this->requiredNonNegativeInt(
                    $row->delivery_outside_zone_surcharge_kopecks,
                    'delivery_outside_zone_surcharge_kopecks',
                ),
                belowThresholdFeeMode: DeliveryFeeMode::BaseTariff,
                inZoneAtThresholdFeeMode: DeliveryFeeMode::Free,
                outsideZoneAtThresholdFeeMode: DeliveryFeeMode::BasePlusSurcharge,
                isActive: (bool) $row->delivery_benefit_active,
            ),
            complementSetBenefitRule: new ComplementSetBenefitRule(
                rollsPerSet: $this->requiredPositiveInt(
                    $row->complement_set_rolls_per_set,
                    'complement_set_rolls_per_set',
                ),
                isActive: (bool) $row->complement_set_benefit_active,
            ),
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
