<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\LoadsSeederJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Справочник товаров из снапшота (без HTTP).
 */
class CatalogSeeder extends Seeder
{
    use LoadsSeederJson;

    public function run(): void
    {
        $payload = $this->loadSeederJson('catalog.json');
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $images = is_array($payload['images'] ?? null) ? $payload['images'] : [];
        if ($items === []) {
            throw new RuntimeException('catalog.json пуст.');
        }

        DB::transaction(function () use ($items, $images): void {
            DB::table('CAT_item_images')->delete();
            DB::table('CAT_items')->delete();

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                DB::table('CAT_items')->insert([
                    'id' => (int) $item['id'],
                    'name' => (string) $item['name'],
                    'slug' => (string) $item['slug'],
                    'sku' => $item['sku'] ?? null,
                    'description' => $item['description'] ?? null,
                    'status' => (string) $item['status'],
                    'is_system' => (bool) ($item['is_system'] ?? false),
                    'is_roll' => (bool) ($item['is_roll'] ?? false),
                    'weight' => $item['weight'] ?? null,
                    'nutrition' => $this->encodeJson($item['nutrition'] ?? null),
                    'ingredients' => $this->encodeJson($item['ingredients'] ?? null),
                    'archived_at' => $item['archived_at'] ?? null,
                    'created_at' => $item['created_at'] ?? now(),
                    'updated_at' => $item['updated_at'] ?? now(),
                ]);
            }

            foreach ($images as $image) {
                if (! is_array($image)) {
                    continue;
                }
                DB::table('CAT_item_images')->insert([
                    'id' => (int) $image['id'],
                    'item_id' => (int) $image['item_id'],
                    'path' => (string) $image['path'],
                    'disk' => (string) ($image['disk'] ?? 'public'),
                    'sort_order' => (int) ($image['sort_order'] ?? 0),
                    'focus' => $this->encodeJson($image['focus'] ?? null),
                    'created_at' => $image['created_at'] ?? now(),
                    'updated_at' => $image['updated_at'] ?? now(),
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
