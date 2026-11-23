<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\LoadsSeederJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Тариф и зоны доставки филиала Говорова из снапшота.
 */
class DeliverySeeder extends Seeder
{
    use LoadsSeederJson;

    public function run(): void
    {
        $payload = $this->loadSeederJson('branch_delivery.json');
        $config = is_array($payload['configuration'] ?? null) ? $payload['configuration'] : null;
        $zones = is_array($payload['zones'] ?? null) ? $payload['zones'] : [];

        if ($config === null || $zones === []) {
            throw new RuntimeException('branch_delivery.json повреждён.');
        }

        DB::transaction(function () use ($config, $zones): void {
            DB::table('DLV_zones')->delete();
            DB::table('DLV_configurations')->delete();

            DB::table('DLV_configurations')->insert([
                'id' => (int) $config['id'],
                'branch_id' => (int) $config['branch_id'],
                'min_order_amount_kopecks' => $config['min_order_amount_kopecks'] ?? null,
                'average_delivery_time_minutes' => $config['average_delivery_time_minutes'] ?? null,
                'created_at' => $config['created_at'] ?? now(),
                'updated_at' => $config['updated_at'] ?? now(),
            ]);

            foreach ($zones as $zone) {
                if (! is_array($zone)) {
                    continue;
                }
                DB::table('DLV_zones')->insert([
                    'id' => (int) $zone['id'],
                    'configuration_id' => (int) $zone['configuration_id'],
                    'zone_key' => (string) $zone['zone_key'],
                    'name' => (string) $zone['name'],
                    'fee_kopecks' => (int) ($zone['fee_kopecks'] ?? 0),
                    'is_remote' => (bool) ($zone['is_remote'] ?? false),
                    'geojson' => $this->encodeJson($zone['geojson'] ?? null),
                    'delivery_product_id' => $zone['delivery_product_id'] ?? null,
                    'sort_order' => (int) ($zone['sort_order'] ?? 0),
                    'created_at' => $zone['created_at'] ?? now(),
                    'updated_at' => $zone['updated_at'] ?? now(),
                ]);
            }
        });
    }

    private function encodeJson(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
