<?php

namespace Database\Seeders;

use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Content\Model\DLV_Configuration;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $kitchenLatitude = 56.5129000;
        $kitchenLongitude = 84.9861000;

        DLV_Configuration::query()->updateOrCreate(
            ['id' => DeliveryConfigurationRepository::SINGLETON_ID],
            [
                'min_order_amount_kopecks' => 100_000,
                'delivery_fee_kopecks' => 40_000,
                'outside_zone_delivery_fee_kopecks' => 20_000,
                'average_delivery_time_minutes' => 90,
                'kitchen_city' => 'Томск',
                'kitchen_street' => 'ул. Говорова',
                'kitchen_house' => '50',
                'kitchen_address' => 'Россия, Томская область, Томск, ул. Говорова, 50',
                'kitchen_latitude' => $kitchenLatitude,
                'kitchen_longitude' => $kitchenLongitude,
                'delivery_zone_geojson' => [
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
                ],
            ],
        );
    }
}
