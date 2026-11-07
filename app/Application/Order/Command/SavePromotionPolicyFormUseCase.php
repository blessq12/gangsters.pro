<?php

namespace App\Application\Order\Command;

use App\Application\Order\Query\GetPromotionPolicyFormUseCase;
use App\Domain\Order\Repository\PromotionPolicyRepository;

final class SavePromotionPolicyFormUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly GetPromotionPolicyFormUseCase $read,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(array $payload): array
    {
        $weekdays = [];
        foreach (is_array($payload['gift_courier_weekdays'] ?? null) ? $payload['gift_courier_weekdays'] : [] as $day) {
            $int = (int) $day;
            if ($int >= 1 && $int <= 7) {
                $weekdays[$int] = $int;
            }
        }
        $days = array_values($weekdays);
        sort($days);
        if ($days === []) {
            throw new \InvalidArgumentException('Отметь хотя бы один день подарка при доставке.');
        }

        $pickup = max(1, (int) ($payload['gift_pickup_min_order_rubles'] ?? 0));
        $courier = max(1, (int) ($payload['gift_courier_min_order_rubles'] ?? 0));

        $this->policies->save([
            'gift_pickup_min_order_kopecks' => $pickup * 100,
            'gift_courier_min_order_kopecks' => $courier * 100,
            'gift_courier_weekdays' => $days,
            'gift_benefit_active' => (bool) ($payload['gift_benefit_active'] ?? false),
            'delivery_free_threshold_kopecks' => max(0, (int) ($payload['delivery_free_threshold_rubles'] ?? 0)) * 100,
            'delivery_outside_zone_surcharge_kopecks' => max(0, (int) ($payload['delivery_outside_zone_surcharge_rubles'] ?? 0)) * 100,
            'delivery_benefit_active' => (bool) ($payload['delivery_benefit_active'] ?? false),
            'complement_set_benefit_active' => (bool) ($payload['complement_set_benefit_active'] ?? false),
            'complement_set_rolls_per_set' => max(1, (int) ($payload['complement_set_rolls_per_set'] ?? 1)),
            'complement_set_max_sets' => max(1, (int) ($payload['complement_set_max_sets'] ?? 1)),
        ]);

        return $this->read->execute();
    }
}
