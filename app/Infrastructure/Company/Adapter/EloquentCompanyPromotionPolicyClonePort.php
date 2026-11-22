<?php

namespace App\Infrastructure\Company\Adapter;

use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Supporting\Company\Port\CompanyPromotionPolicyClonePort;

final class EloquentCompanyPromotionPolicyClonePort implements CompanyPromotionPolicyClonePort
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
    ) {}

    public function exportFlatFields(int $branchId): ?array
    {
        $policy = $this->policies->findByBranch($branchId);
        if (! $policy instanceof PromotionPolicy) {
            return null;
        }

        $pickup = $policy->giftRuleForChannel('pickup') ?? [];
        $courier = $policy->giftRuleForChannel('courier') ?? [];
        $delivery = $policy->deliveryBenefit();
        $complement = $policy->complementSetBenefit();

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
        $this->policies->saveForBranch($branchId, $fields);
    }

    public function existsForBranch(int $branchId): bool
    {
        return $this->policies->findByBranch($branchId) instanceof PromotionPolicy;
    }
}
