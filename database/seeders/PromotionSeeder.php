<?php

namespace Database\Seeders;

use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Infrastructure\Promotion\Model\PRM_Configuration;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        PRM_Configuration::query()->updateOrCreate(
            ['id' => PromotionPolicyRepository::SINGLETON_ID],
            [
                'gift_pickup_min_order_kopecks' => 100_000,
                'gift_courier_min_order_kopecks' => 180_000,
                'gift_benefit_active' => true,
                'delivery_free_threshold_kopecks' => 100_000,
                'delivery_outside_zone_surcharge_kopecks' => 20_000,
                'delivery_benefit_active' => true,
                'complement_set_benefit_active' => true,
                'complement_set_rolls_per_set' => 2,
            ],
        );
    }
}
