<?php

namespace Database\Seeders;

use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use App\Infrastructure\Delivery\Model\DLV_Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $kitchenLatitude = 56.5129000;
        $kitchenLongitude = 84.9861000;

        $geometry = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [$kitchenLongitude - 0.04, $kitchenLatitude - 0.03],
                    [$kitchenLongitude + 0.04, $kitchenLatitude - 0.03],
                    [$kitchenLongitude + 0.04, $kitchenLatitude + 0.03],
                    [$kitchenLongitude - 0.04, $kitchenLatitude + 0.03],
                    [$kitchenLongitude - 0.04, $kitchenLatitude - 0.03],
                ],
            ],
        ];

        DB::transaction(function () use ($kitchenLatitude, $kitchenLongitude, $geometry): void {
            $config = DLV_Configuration::query()->updateOrCreate(
                ['id' => DeliveryConfigurationRepository::SINGLETON_ID],
                [
                    'min_order_amount_kopecks' => 100_000,
                    'average_delivery_time_minutes' => 90,
                    'kitchen_address' => [
                        'city' => 'Томск',
                        'street' => 'ул. Говорова',
                        'house' => '50',
                        'comment' => null,
                        'search_line' => 'Россия, Томская область, Томск, ул. Говорова, 50',
                    ],
                    'kitchen_latitude' => $kitchenLatitude,
                    'kitchen_longitude' => $kitchenLongitude,
                    'company_kitchen_id' => null,
                ],
            );

            DLV_Zone::query()->updateOrCreate(
                [
                    'configuration_id' => $config->id,
                    'zone_key' => 'zone-main',
                ],
                [
                    'name' => 'Основная',
                    'fee_kopecks' => 40_000,
                    'is_remote' => false,
                    'geojson' => $geometry,
                    'delivery_product_id' => null,
                    'sort_order' => 0,
                ],
            );
        });
    }
}
