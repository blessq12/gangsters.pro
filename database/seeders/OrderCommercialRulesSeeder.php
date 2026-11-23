<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\LoadsSeederJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Коммерческие правила оформления заказа филиала Говорова.
 */
class OrderCommercialRulesSeeder extends Seeder
{
    use LoadsSeederJson;

    public function run(): void
    {
        $payload = $this->loadSeederJson('branch_commercial_rules.json');
        if ($payload === [] || ! isset($payload['branch_id'])) {
            throw new RuntimeException('branch_commercial_rules.json повреждён.');
        }

        DB::transaction(function () use ($payload): void {
            DB::table('ORD_branch_commercial_rules')->delete();
            DB::table('ORD_branch_commercial_rules')->insert([
                'id' => (int) $payload['id'],
                'branch_id' => (int) $payload['branch_id'],
                'gift_rules' => $this->encodeJson($payload['gift_rules'] ?? []),
                'delivery_benefit' => $this->encodeJson($payload['delivery_benefit'] ?? []),
                'complement_set_benefit' => $this->encodeJson($payload['complement_set_benefit'] ?? []),
                'created_at' => $payload['created_at'] ?? now(),
                'updated_at' => $payload['updated_at'] ?? now(),
            ]);
        });
    }

    private function encodeJson(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}
