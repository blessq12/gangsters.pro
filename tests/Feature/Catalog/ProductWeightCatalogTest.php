<?php

namespace Tests\Feature\Catalog;

use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\ApiTestCase;

final class ProductWeightCatalogTest extends ApiTestCase
{
    public function test_catalog_exposes_product_weight(): void
    {
        if (! Schema::hasColumn('PRD_products', 'weight')) {
            $this->markTestSkipped('Column weight is missing — run migrations.');
        }

        $suffix = bin2hex(random_bytes(3));

        $category = PRD_Category::query()->create([
            'name' => 'Weight Cat '.$suffix,
            'slug' => 'weight-cat-'.$suffix,
            'sort_order' => 9997,
            'is_active' => true,
            'is_accompanying' => false,
        ]);

        $product = PRD_Product::query()->create([
            'name' => 'Weight Product '.$suffix,
            'slug' => 'weight-product-'.$suffix,
            'sku' => 'W-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 500,
            'weight' => 280,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        DB::table('PRD_category_product')->insert([
            'category_id' => $category->id,
            'product_id' => $product->id,
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/catalog');
        $response->assertOk();

        $match = $this->findCatalogItemById($response->json('categories') ?? [], (int) $product->id);
        $this->assertNotNull($match);
        $this->assertSame(280, $match['weight'] ?? null);
    }

    public function test_catalog_set_weight_is_sum_of_line_products(): void
    {
        if (! Schema::hasColumn('PRD_products', 'weight')) {
            $this->markTestSkipped('Column weight is missing — run migrations.');
        }

        if (! Schema::hasTable('PRD_product_set_lines')) {
            $this->markTestSkipped('Table PRD_product_set_lines is missing.');
        }

        $suffix = bin2hex(random_bytes(3));

        $category = PRD_Category::query()->create([
            'name' => 'Weight Set Cat '.$suffix,
            'slug' => 'weight-set-cat-'.$suffix,
            'sort_order' => 9996,
            'is_active' => true,
            'is_accompanying' => false,
        ]);

        $lineA = PRD_Product::query()->create([
            'name' => 'Line A '.$suffix,
            'slug' => 'line-a-'.$suffix,
            'sku' => 'LA-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 100,
            'weight' => 100,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        $lineB = PRD_Product::query()->create([
            'name' => 'Line B '.$suffix,
            'slug' => 'line-b-'.$suffix,
            'sku' => 'LB-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 200,
            'weight' => 50,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        $set = PRD_Product::query()->create([
            'name' => 'Weight Set '.$suffix,
            'slug' => 'weight-set-'.$suffix,
            'sku' => 'WS-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'set',
            'is_system' => false,
            'price' => 700,
            'weight' => null,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        DB::table('PRD_product_set_lines')->insert([
            [
                'set_id' => $set->id,
                'product_id' => $lineA->id,
                'quantity' => 2,
                'sort_order' => 0,
            ],
            [
                'set_id' => $set->id,
                'product_id' => $lineB->id,
                'quantity' => 1,
                'sort_order' => 1,
            ],
        ]);

        DB::table('PRD_category_product')->insert([
            'category_id' => $category->id,
            'product_id' => $set->id,
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/catalog');
        $response->assertOk();

        $match = $this->findCatalogItemById($response->json('categories') ?? [], (int) $set->id);
        $this->assertNotNull($match);
        $this->assertSame('set', $match['kind'] ?? null);
        // 100*2 + 50*1 = 250 — вес у всех позиций
        $this->assertSame(250, $match['weight'] ?? null);
    }

    public function test_catalog_set_weight_null_when_any_line_without_weight(): void
    {
        if (! Schema::hasColumn('PRD_products', 'weight')) {
            $this->markTestSkipped('Column weight is missing — run migrations.');
        }

        if (! Schema::hasTable('PRD_product_set_lines')) {
            $this->markTestSkipped('Table PRD_product_set_lines is missing.');
        }

        $suffix = bin2hex(random_bytes(3));

        $category = PRD_Category::query()->create([
            'name' => 'Partial Weight Set Cat '.$suffix,
            'slug' => 'partial-weight-set-cat-'.$suffix,
            'sort_order' => 9995,
            'is_active' => true,
            'is_accompanying' => false,
        ]);

        $withWeight = PRD_Product::query()->create([
            'name' => 'Has Weight '.$suffix,
            'slug' => 'has-weight-'.$suffix,
            'sku' => 'HW-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 100,
            'weight' => 120,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        $withoutWeight = PRD_Product::query()->create([
            'name' => 'No Weight '.$suffix,
            'slug' => 'no-weight-'.$suffix,
            'sku' => 'NW-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 200,
            'weight' => null,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        $set = PRD_Product::query()->create([
            'name' => 'Partial Weight Set '.$suffix,
            'slug' => 'partial-weight-set-'.$suffix,
            'sku' => 'PWS-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'set',
            'is_system' => false,
            'price' => 700,
            'weight' => null,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        DB::table('PRD_product_set_lines')->insert([
            [
                'set_id' => $set->id,
                'product_id' => $withWeight->id,
                'quantity' => 1,
                'sort_order' => 0,
            ],
            [
                'set_id' => $set->id,
                'product_id' => $withoutWeight->id,
                'quantity' => 1,
                'sort_order' => 1,
            ],
        ]);

        DB::table('PRD_category_product')->insert([
            'category_id' => $category->id,
            'product_id' => $set->id,
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/catalog');
        $response->assertOk();

        $match = $this->findCatalogItemById($response->json('categories') ?? [], (int) $set->id);
        $this->assertNotNull($match);
        $this->assertArrayHasKey('weight', $match);
        $this->assertNull($match['weight']);
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @return array<string, mixed>|null
     */
    private function findCatalogItemById(array $categories, int $id): ?array
    {
        foreach ($categories as $node) {
            foreach ($node['items'] ?? [] as $item) {
                if ((int) ($item['id'] ?? 0) === $id) {
                    return $item;
                }
            }
        }

        return null;
    }
}
