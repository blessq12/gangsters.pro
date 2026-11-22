<?php

namespace Database\Seeders;

use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use App\Infrastructure\Delivery\Model\DLV_Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Тариф и зоны доставки основного филиала (Томск) из снапшота старой DLV_configuration.
 * Филиал должен уже существовать (CompanySeeder).
 */
class DeliverySeeder extends Seeder
{
    private const DATA_FILE = __DIR__.'/data/primary_branch_delivery_zones.json';

    public function run(): void
    {
        $payload = $this->loadPayload();
        $branch = $this->resolvePrimaryBranch();

        $minOrder = (int) ($payload['min_order_amount_kopecks'] ?? 120_000);
        $avgMinutes = (int) ($payload['average_delivery_time_minutes'] ?? 90);
        $zones = is_array($payload['zones'] ?? null) ? $payload['zones'] : [];
        if ($zones === []) {
            throw new RuntimeException('В снапшоте зон доставки нет позиций.');
        }

        DB::transaction(function () use ($branch, $minOrder, $avgMinutes, $zones): void {
            $config = DLV_Configuration::query()->updateOrCreate(
                ['branch_id' => $branch->id],
                [
                    'min_order_amount_kopecks' => $minOrder,
                    'average_delivery_time_minutes' => $avgMinutes,
                ],
            );

            $keptKeys = [];
            foreach ($zones as $index => $zone) {
                if (! is_array($zone)) {
                    continue;
                }

                $zoneKey = trim((string) ($zone['zone_key'] ?? ''));
                if ($zoneKey === '') {
                    throw new RuntimeException(sprintf('У зоны #%d пустой zone_key.', $index));
                }

                $geojson = $zone['geojson'] ?? null;
                if (! is_array($geojson)) {
                    throw new RuntimeException(sprintf('У зоны %s нет geojson.', $zoneKey));
                }

                $keptKeys[] = $zoneKey;

                DLV_Zone::query()->updateOrCreate(
                    [
                        'configuration_id' => $config->id,
                        'zone_key' => $zoneKey,
                    ],
                    [
                        'name' => (string) ($zone['name'] ?? $zoneKey),
                        'fee_kopecks' => (int) ($zone['fee_kopecks'] ?? 0),
                        'is_remote' => (bool) ($zone['is_remote'] ?? false),
                        'geojson' => $geojson,
                        'delivery_product_id' => isset($zone['delivery_product_id']) && $zone['delivery_product_id'] !== null
                            ? (int) $zone['delivery_product_id']
                            : null,
                        'sort_order' => (int) ($zone['sort_order'] ?? $index),
                    ],
                );
            }

            DLV_Zone::query()
                ->where('configuration_id', $config->id)
                ->whereNotIn('zone_key', $keptKeys)
                ->delete();
        });
    }

    private function resolvePrimaryBranch(): CMP_Branch
    {
        $branch = CMP_Branch::query()
            ->where('is_active', true)
            ->where('name', 'Томск')
            ->orderBy('id')
            ->first();

        if ($branch instanceof CMP_Branch) {
            return $branch;
        }

        $branch = CMP_Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $branch instanceof CMP_Branch) {
            throw new RuntimeException('Нет филиалов: сначала запустите CompanySeeder.');
        }

        return $branch;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadPayload(): array
    {
        if (! is_file(self::DATA_FILE)) {
            throw new RuntimeException('Не найден файл зон: '.self::DATA_FILE);
        }

        $raw = file_get_contents(self::DATA_FILE);
        if ($raw === false || $raw === '') {
            throw new RuntimeException('Пустой файл зон доставки.');
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            throw new RuntimeException('Некорректный JSON зон доставки.');
        }

        return $payload;
    }
}
