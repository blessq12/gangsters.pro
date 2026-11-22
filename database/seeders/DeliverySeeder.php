<?php

namespace Database\Seeders;

use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use App\Infrastructure\Delivery\Model\DLV_Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Тариф доставки из /api/content → DLV_configurations / DLV_zones на каждый активный филиал.
 * Филиалы должны уже существовать (CompanySeeder).
 */
class DeliverySeeder extends Seeder
{
    private const CONTENT_SOURCE_URL = 'https://gangsta-sushi.ru/api/content';

    public function run(): void
    {
        $payload = $this->fetchContentPayload();
        $delivery = is_array($payload['delivery'] ?? null) ? $payload['delivery'] : [];
        $settings = is_array($delivery['settings'] ?? null) ? $delivery['settings'] : [];
        $zone = is_array($delivery['zone'] ?? null) ? $delivery['zone'] : [];

        $branches = CMP_Branch::query()->where('is_active', true)->orderBy('id')->get();
        if ($branches->isEmpty()) {
            throw new RuntimeException('Нет филиалов: сначала запустите CompanySeeder.');
        }

        $feeKopecks = (int) ($settings['delivery_fee_kopecks'] ?? 40_000);
        $minOrder = (int) ($settings['min_order_amount_kopecks'] ?? 100_000);
        $avgMinutes = (int) ($settings['average_delivery_time_minutes'] ?? 90);
        $sourceGeometry = is_array($zone['delivery_zone_geojson'] ?? null)
            ? $zone['delivery_zone_geojson']
            : null;

        DB::transaction(function () use ($branches, $sourceGeometry, $feeKopecks, $minOrder, $avgMinutes, $zone): void {
            foreach ($branches as $branch) {
                $branchLatitude = $branch->latitude !== null
                    ? (float) $branch->latitude
                    : (isset($zone['kitchen_latitude']) ? (float) $zone['kitchen_latitude'] : 56.513432);
                $branchLongitude = $branch->longitude !== null
                    ? (float) $branch->longitude
                    : (isset($zone['kitchen_longitude']) ? (float) $zone['kitchen_longitude'] : 84.986366);

                $geometry = $sourceGeometry ?? [
                    'type' => 'Polygon',
                    'coordinates' => [
                        [
                            [$branchLongitude - 0.04, $branchLatitude - 0.03],
                            [$branchLongitude + 0.04, $branchLatitude - 0.03],
                            [$branchLongitude + 0.04, $branchLatitude + 0.03],
                            [$branchLongitude - 0.04, $branchLatitude + 0.03],
                            [$branchLongitude - 0.04, $branchLatitude - 0.03],
                        ],
                    ],
                ];

                $config = DLV_Configuration::query()->updateOrCreate(
                    ['branch_id' => $branch->id],
                    [
                        'min_order_amount_kopecks' => $minOrder,
                        'average_delivery_time_minutes' => $avgMinutes,
                    ],
                );

                DLV_Zone::query()->updateOrCreate(
                    [
                        'configuration_id' => $config->id,
                        'zone_key' => 'zone-main',
                    ],
                    [
                        'name' => 'Основная',
                        'fee_kopecks' => $feeKopecks,
                        'is_remote' => false,
                        'geojson' => $geometry,
                        'delivery_product_id' => null,
                        'sort_order' => 0,
                    ],
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchContentPayload(): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->retry(2, 500)
            ->get(self::CONTENT_SOURCE_URL);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Не удалось загрузить content: HTTP '.$response->status(),
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Некорректный формат content API.');
        }

        return $payload;
    }
}
