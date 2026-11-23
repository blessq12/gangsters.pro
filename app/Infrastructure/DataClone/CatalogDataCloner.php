<?php

namespace App\Infrastructure\DataClone;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * PRD_* → CAT_items + CMP_branch_* (меню филиала).
 */
final class CatalogDataCloner
{
    /**
     * @return array{
     *     products: int,
     *     images: int,
     *     menu_items: int,
     *     categories: int,
     *     category_items: int,
     *     sets: int,
     *     set_lines: int,
     *     tags: int,
     *     entry_tags: int
     * }
     */
    public function clone(Connection $source, int $branchId): array
    {
        $products = $source->table('PRD_products')->orderBy('id')->get();
        $images = $source->table('PRD_product_images')->orderBy('id')->get();
        $categories = $source->table('PRD_categories')->orderBy('id')->get();
        $categoryProduct = $source->table('PRD_category_product')->orderBy('id')->get();
        $setLines = $source->table('PRD_product_set_lines')->orderBy('id')->get();
        $tags = $source->table('PRD_tags')->orderBy('id')->get();
        $productTags = $source->table('PRD_product_tag')->orderBy('id')->get();

        $kindById = [];
        foreach ($products as $product) {
            $kindById[(int) $product->id] = (string) $product->catalog_kind;
        }

        $stats = [
            'products' => 0,
            'images' => 0,
            'menu_items' => 0,
            'categories' => 0,
            'category_items' => 0,
            'sets' => 0,
            'set_lines' => 0,
            'tags' => 0,
            'entry_tags' => 0,
        ];

        DB::transaction(function () use (
            $products,
            $images,
            $categories,
            $categoryProduct,
            $setLines,
            $tags,
            $productTags,
            $kindById,
            $branchId,
            &$stats,
        ): void {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            DB::table('CMP_branch_entry_tags')->delete();
            DB::table('CMP_branch_category_items')->delete();
            DB::table('CMP_branch_set_lines')->delete();
            DB::table('CMP_branch_sets')->where('branch_id', $branchId)->delete();
            DB::table('CMP_branch_tags')->where('branch_id', $branchId)->delete();
            DB::table('CMP_branch_categories')->where('branch_id', $branchId)->delete();
            DB::table('CMP_branch_menu_items')->where('branch_id', $branchId)->delete();
            DB::table('CAT_tech_card_lines')->delete();
            DB::table('CAT_tech_cards')->delete();
            DB::table('CAT_item_images')->delete();
            DB::table('CAT_items')->delete();

            foreach ($products as $product) {
                $id = (int) $product->id;
                $kind = (string) $product->catalog_kind;

                if ($kind === 'set') {
                    DB::table('CMP_branch_sets')->insert([
                        'id' => $id,
                        'branch_id' => $branchId,
                        'name' => (string) $product->name,
                        'slug' => (string) $product->slug,
                        'description' => $product->description,
                        'is_available' => (string) $product->status === 'active',
                        'price_kopecks' => $this->rublesToKopecks($product->price),
                        'status' => (string) $product->status,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                    ]);
                    $stats['sets']++;

                    continue;
                }

                DB::table('CAT_items')->insert([
                    'id' => $id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'status' => (string) $product->status,
                    'is_system' => (bool) $product->is_system,
                    'is_roll' => (bool) $product->meta_counts_as_roll,
                    'weight' => $product->weight !== null ? (int) $product->weight : null,
                    'nutrition' => json_encode([
                        'calories' => (float) $product->calories,
                        'proteins' => (float) $product->proteins,
                        'fats' => (float) $product->fats,
                        'carbs' => (float) $product->carbs,
                        'basis' => (string) $product->nutrition_basis,
                    ], JSON_UNESCAPED_UNICODE),
                    'ingredients' => $this->jsonOrNull($product->ingredients),
                    'archived_at' => $product->archived_at,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ]);
                $stats['products']++;

                if ($product->price !== null) {
                    DB::table('CMP_branch_menu_items')->insert([
                        'branch_id' => $branchId,
                        'catalog_item_id' => $id,
                        'is_available' => (string) $product->status === 'active',
                        'price_kopecks' => $this->rublesToKopecks($product->price),
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                    ]);
                    $stats['menu_items']++;
                }
            }

            foreach ($images as $image) {
                $productId = (int) $image->product_id;
                if (($kindById[$productId] ?? null) !== 'product') {
                    continue;
                }

                $focus = null;
                if ($image->focus_x !== null || $image->focus_y !== null) {
                    $focus = json_encode([
                        'x' => $image->focus_x !== null ? (float) $image->focus_x : null,
                        'y' => $image->focus_y !== null ? (float) $image->focus_y : null,
                    ]);
                }

                DB::table('CAT_item_images')->insert([
                    'id' => (int) $image->id,
                    'item_id' => $productId,
                    'path' => (string) $image->path,
                    'disk' => (string) ($image->disk ?: 'public'),
                    'sort_order' => (int) $image->sort_order,
                    'focus' => $focus,
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ]);
                $stats['images']++;
            }

            foreach ($categories as $category) {
                DB::table('CMP_branch_categories')->insert([
                    'id' => (int) $category->id,
                    'branch_id' => $branchId,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug,
                    'sort_order' => (int) $category->sort_order,
                    'is_active' => (bool) $category->is_active,
                    'is_accompanying' => (bool) $category->is_accompanying,
                    'is_complement' => (bool) $category->is_addon,
                    'is_gift' => false,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ]);
                $stats['categories']++;
            }

            foreach ($categoryProduct as $link) {
                $productId = (int) $link->product_id;
                $kind = $kindById[$productId] ?? null;
                if ($kind === null) {
                    continue;
                }

                $entryKind = $kind === 'set' ? 'set' : 'product';
                DB::table('CMP_branch_category_items')->insert([
                    'id' => (int) $link->id,
                    'category_id' => (int) $link->category_id,
                    'entry_kind' => $entryKind,
                    'entry_id' => $productId,
                    'sort_order' => (int) $link->sort_order,
                ]);
                $stats['category_items']++;
            }

            foreach ($setLines as $line) {
                $setId = (int) $line->set_id;
                $catalogItemId = (int) $line->product_id;
                if (($kindById[$setId] ?? null) !== 'set') {
                    continue;
                }
                if (($kindById[$catalogItemId] ?? null) !== 'product') {
                    continue;
                }

                DB::table('CMP_branch_set_lines')->insert([
                    'id' => (int) $line->id,
                    'set_id' => $setId,
                    'catalog_item_id' => $catalogItemId,
                    'quantity' => (int) $line->quantity,
                ]);
                $stats['set_lines']++;
            }

            foreach ($tags as $tag) {
                DB::table('CMP_branch_tags')->insert([
                    'id' => (int) $tag->id,
                    'branch_id' => $branchId,
                    'code' => (string) $tag->code,
                    'label' => (string) $tag->label,
                    'color' => (string) $tag->color,
                    'is_active' => (bool) $tag->is_active,
                    'sort_order' => (int) $tag->sort_order,
                    'created_at' => $tag->created_at,
                    'updated_at' => $tag->updated_at,
                ]);
                $stats['tags']++;
            }

            foreach ($productTags as $link) {
                $productId = (int) $link->product_id;
                $kind = $kindById[$productId] ?? null;
                if ($kind === null) {
                    continue;
                }

                DB::table('CMP_branch_entry_tags')->insert([
                    'id' => (int) $link->id,
                    'tag_id' => (int) $link->tag_id,
                    'entry_kind' => $kind === 'set' ? 'set' : 'product',
                    'entry_id' => $productId,
                ]);
                $stats['entry_tags']++;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return $stats;
    }

    private function rublesToKopecks(mixed $rubles): int
    {
        if ($rubles === null) {
            return 0;
        }

        return (int) $rubles * 100;
    }

    private function jsonOrNull(mixed $value): ?string
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
