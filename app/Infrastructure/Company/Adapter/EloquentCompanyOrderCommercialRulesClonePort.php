<?php

namespace App\Infrastructure\Company\Adapter;

use App\Domain\Core\Order\Entity\OrderCommercialRules;
use App\Domain\Core\Order\Repository\OrderCommercialRulesRepository;
use App\Domain\Supporting\Company\Port\CompanyOrderCommercialRulesClonePort;

final class EloquentCompanyOrderCommercialRulesClonePort implements CompanyOrderCommercialRulesClonePort
{
    public function __construct(
        private readonly OrderCommercialRulesRepository $commercialRules,
    ) {}

    public function exportFlatFields(int $branchId): ?array
    {
        $rules = $this->commercialRules->findByBranch($branchId);
        if (! $rules instanceof OrderCommercialRules) {
            return null;
        }

        $pickup = $rules->giftRuleForChannel('pickup') ?? [];
        $courier = $rules->giftRuleForChannel('courier') ?? [];
        $delivery = $rules->deliveryBenefit();
        $complement = $rules->complementSetBenefit();

        $weekdays = [];
        foreach ($courier['allowed_weekdays'] ?? [] as $day) {
            $int = (int) $day;
            if ($int >= 1 && $int <= 7) {
                $weekdays[$int] = $int;
            }
        }
        $weekdays = array_values($weekdays);
        sort($weekdays);

        return [
            'gift_pickup_min_order_kopecks' => max(1, (int) ($pickup['min_order_amount_kopecks'] ?? 1)),
            'gift_courier_min_order_kopecks' => max(1, (int) ($courier['min_order_amount_kopecks'] ?? 1)),
            'gift_courier_weekdays' => $weekdays,
            'gift_benefit_active' => (bool) (($pickup['is_active'] ?? false) || ($courier['is_active'] ?? false)),
            'delivery_free_threshold_kopecks' => max(0, (int) ($delivery['free_delivery_threshold_kopecks'] ?? 0)),
            'delivery_outside_zone_surcharge_kopecks' => 0,
            'delivery_benefit_active' => (bool) ($delivery['is_active'] ?? false),
            'complement_set_benefit_active' => (bool) ($complement['is_active'] ?? false),
            'complement_set_rolls_per_set' => max(1, (int) ($complement['rolls_per_set'] ?? 1)),
            'complement_set_max_sets' => max(1, (int) ($complement['max_sets'] ?? 1)),
        ];
    }

    public function importFlatFields(int $branchId, array $fields): void
    {
        $this->commercialRules->saveForBranch($branchId, $fields);
    }

    public function existsForBranch(int $branchId): bool
    {
        return $this->commercialRules->findByBranch($branchId) instanceof OrderCommercialRules;
    }
}
