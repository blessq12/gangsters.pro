<?php

namespace Database\Seeders;

use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Order\Model\PRM_Policy;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CMP_Branch::query()->orderBy('id')->pluck('id') as $branchId) {
            PRM_Policy::query()->updateOrCreate(
                ['branch_id' => $branchId],
                [
                    'gift_rules' => [
                        [
                            'order_channel' => 'pickup',
                            'min_order_amount_kopecks' => 100_000,
                            'benefit_type' => 'free_roll_gift',
                            'is_active' => true,
                            'allowed_weekdays' => [],
                        ],
                        [
                            'order_channel' => 'courier',
                            'min_order_amount_kopecks' => 180_000,
                            'benefit_type' => 'free_roll_gift',
                            'is_active' => true,
                            'allowed_weekdays' => [1, 2, 3, 4],
                        ],
                    ],
                    'delivery_benefit' => [
                        'free_delivery_threshold_kopecks' => 100_000,
                        'outside_zone_surcharge_kopecks' => 20_000,
                        'below_threshold_fee_mode' => 'base_tariff',
                        'in_zone_at_threshold_fee_mode' => 'free',
                        'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
                        'is_active' => true,
                    ],
                    'complement_set_benefit' => [
                        'rolls_per_set' => 2,
                        'max_sets' => 3,
                        'is_active' => true,
                    ],
                ],
            );
        }
    }
}
