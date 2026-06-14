<?php

namespace Database\Seeders;

use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        DLV_Configuration::query()->updateOrCreate(
            ['id' => DeliveryConfigurationRepository::SINGLETON_ID],
            [
                'min_order_amount_kopecks' => 150_000,
                'delivery_fee_kopecks' => 20_000,
                'outside_zone_delivery_fee_kopecks' => 50_000,
                'average_delivery_time_minutes' => 45,
                'kitchen_city' => 'Томск',
                'kitchen_street' => 'пр. Ленина',
                'kitchen_house' => '1',
                'kitchen_address' => 'Томск, пр. Ленина, 1',
                'kitchen_latitude' => 56.4845800,
                'kitchen_longitude' => 84.9481700,
                'delivery_zone_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [
                        [
                            [84.90, 56.46],
                            [85.02, 56.46],
                            [85.02, 56.51],
                            [84.90, 56.51],
                            [84.90, 56.46],
                        ],
                    ],
                ],
            ],
        );
    }
}
