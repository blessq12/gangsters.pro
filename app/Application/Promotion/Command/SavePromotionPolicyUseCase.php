<?php

namespace App\Application\Promotion\Command;

use App\Application\Promotion\DTO\SavePromotionPolicyDto;
use App\Application\Promotion\Query\GetAdminPromotionPolicyUseCase;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;

final class SavePromotionPolicyUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly GetAdminPromotionPolicyUseCase $read,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SavePromotionPolicyDto $dto): array
    {
        if ($dto->giftPickupMinOrderKopecks < 0 || $dto->giftCourierMinOrderKopecks < 0) {
            throw new \InvalidArgumentException('Пороги подарка не могут быть отрицательными.');
        }

        if ($dto->deliveryFreeThresholdKopecks < 0 || $dto->deliveryOutsideZoneSurchargeKopecks < 0) {
            throw new \InvalidArgumentException('Пороги доставки не могут быть отрицательными.');
        }

        if ($dto->complementSetRollsPerSet < 1 || $dto->complementSetMaxSets < 1) {
            throw new \InvalidArgumentException('rolls_per_set и max_sets должны быть ≥ 1.');
        }

        $weekdays = [];
        foreach ($dto->giftCourierWeekdays as $day) {
            $int = (int) $day;
            if ($int >= 1 && $int <= 7) {
                $weekdays[$int] = $int;
            }
        }
        $weekdays = array_values($weekdays);
        sort($weekdays);

        $this->policies->save([
            'gift_pickup_min_order_kopecks' => $dto->giftPickupMinOrderKopecks,
            'gift_courier_min_order_kopecks' => $dto->giftCourierMinOrderKopecks,
            'gift_courier_weekdays' => $weekdays,
            'gift_benefit_active' => $dto->giftBenefitActive,
            'delivery_free_threshold_kopecks' => $dto->deliveryFreeThresholdKopecks,
            'delivery_outside_zone_surcharge_kopecks' => $dto->deliveryOutsideZoneSurchargeKopecks,
            'delivery_benefit_active' => $dto->deliveryBenefitActive,
            'complement_set_benefit_active' => $dto->complementSetBenefitActive,
            'complement_set_rolls_per_set' => $dto->complementSetRollsPerSet,
            'complement_set_max_sets' => $dto->complementSetMaxSets,
        ]);

        return $this->read->execute();
    }
}
