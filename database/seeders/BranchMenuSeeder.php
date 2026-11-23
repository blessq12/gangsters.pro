<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\LoadsSeederJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Меню филиала Говорова: категории/теги/сеты/цены из снапшота.
 */
class BranchMenuSeeder extends Seeder
{
    use LoadsSeederJson;

    public function run(): void
    {
        $payload = $this->loadSeederJson('branch_menu.json');
        $categories = is_array($payload['categories'] ?? null) ? $payload['categories'] : [];
        $categoryItems = is_array($payload['category_items'] ?? null) ? $payload['category_items'] : [];
        $menuItems = is_array($payload['menu_items'] ?? null) ? $payload['menu_items'] : [];
        $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
        $entryTags = is_array($payload['entry_tags'] ?? null) ? $payload['entry_tags'] : [];
        $sets = is_array($payload['sets'] ?? null) ? $payload['sets'] : [];
        $setLines = is_array($payload['set_lines'] ?? null) ? $payload['set_lines'] : [];

        if ($menuItems === [] || $categories === []) {
            throw new RuntimeException('branch_menu.json: нет меню или категорий.');
        }

        $catalogIds = DB::table('CAT_items')->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        $catalogIdSet = array_fill_keys($catalogIds, true);

        DB::transaction(function () use (
            $categories,
            $categoryItems,
            $menuItems,
            $tags,
            $entryTags,
            $sets,
            $setLines,
            $catalogIdSet,
        ): void {
            DB::table('CMP_branch_entry_tags')->delete();
            DB::table('CMP_branch_category_items')->delete();
            DB::table('CMP_branch_set_lines')->delete();
            DB::table('CMP_branch_sets')->delete();
            DB::table('CMP_branch_tags')->delete();
            DB::table('CMP_branch_categories')->delete();
            DB::table('CMP_branch_menu_items')->delete();

            foreach ($menuItems as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $catalogItemId = (int) $row['catalog_item_id'];
                if (! isset($catalogIdSet[$catalogItemId])) {
                    continue;
                }
                DB::table('CMP_branch_menu_items')->insert([
                    'id' => (int) $row['id'],
                    'branch_id' => (int) $row['branch_id'],
                    'catalog_item_id' => $catalogItemId,
                    'is_available' => (bool) ($row['is_available'] ?? true),
                    'price_kopecks' => (int) $row['price_kopecks'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]);
            }

            foreach ($categories as $row) {
                if (! is_array($row)) {
                    continue;
                }
                DB::table('CMP_branch_categories')->insert([
                    'id' => (int) $row['id'],
                    'branch_id' => (int) $row['branch_id'],
                    'name' => (string) $row['name'],
                    'slug' => (string) $row['slug'],
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'is_accompanying' => (bool) ($row['is_accompanying'] ?? false),
                    'is_complement' => (bool) ($row['is_complement'] ?? false),
                    'is_gift' => (bool) ($row['is_gift'] ?? false),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]);
            }

            foreach ($categoryItems as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $entryKind = (string) $row['entry_kind'];
                $entryId = (int) $row['entry_id'];
                if ($entryKind === 'product' && ! isset($catalogIdSet[$entryId])) {
                    continue;
                }
                DB::table('CMP_branch_category_items')->insert([
                    'id' => (int) $row['id'],
                    'category_id' => (int) $row['category_id'],
                    'entry_kind' => $entryKind,
                    'entry_id' => $entryId,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ]);
            }

            foreach ($tags as $row) {
                if (! is_array($row)) {
                    continue;
                }
                DB::table('CMP_branch_tags')->insert([
                    'id' => (int) $row['id'],
                    'branch_id' => (int) $row['branch_id'],
                    'code' => (string) $row['code'],
                    'label' => (string) $row['label'],
                    'color' => (string) $row['color'],
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]);
            }

            foreach ($entryTags as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $entryKind = (string) $row['entry_kind'];
                $entryId = (int) $row['entry_id'];
                if ($entryKind === 'product' && ! isset($catalogIdSet[$entryId])) {
                    continue;
                }
                DB::table('CMP_branch_entry_tags')->insert([
                    'id' => (int) $row['id'],
                    'tag_id' => (int) $row['tag_id'],
                    'entry_kind' => $entryKind,
                    'entry_id' => $entryId,
                ]);
            }

            foreach ($sets as $row) {
                if (! is_array($row)) {
                    continue;
                }
                DB::table('CMP_branch_sets')->insert([
                    'id' => (int) $row['id'],
                    'branch_id' => (int) $row['branch_id'],
                    'name' => (string) $row['name'],
                    'slug' => (string) $row['slug'],
                    'description' => $row['description'] ?? null,
                    'is_available' => (bool) ($row['is_available'] ?? true),
                    'price_kopecks' => (int) ($row['price_kopecks'] ?? 0),
                    'status' => (string) ($row['status'] ?? 'active'),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]);
            }

            foreach ($setLines as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $catalogItemId = (int) $row['catalog_item_id'];
                if (! isset($catalogIdSet[$catalogItemId])) {
                    continue;
                }
                DB::table('CMP_branch_set_lines')->insert([
                    'id' => (int) $row['id'],
                    'set_id' => (int) $row['set_id'],
                    'catalog_item_id' => $catalogItemId,
                    'quantity' => (int) $row['quantity'],
                ]);
            }
        });
    }
}
