<?php

namespace Database\Seeders;

use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Company\Model\CMP_Kitchen;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use App\Infrastructure\Delivery\Model\DLV_Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $kitchen = CMP_Kitchen::query()->orderBy('id')->first();
        $kitchenLatitude = $kitchen?->latitude !== null ? (float) $kitchen->latitude : 56.5129000;
        $kitchenLongitude = $kitchen?->longitude !== null ? (float) $kitchen->longitude : 84.9861000;

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

        DB::transaction(function () use ($kitchen, $geometry): void {
            $config = DLV_Configuration::query()->updateOrCreate(
                ['id' => DeliveryConfigurationRepository::SINGLETON_ID],
                [
                    'min_order_amount_kopecks' => 100_000,
                    'average_delivery_time_minutes' => 90,
                    'company_kitchen_id' => $kitchen?->id,
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
