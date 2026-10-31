<?php

namespace Tests\Feature\Catalog;

use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\ApiTestCase;

final class OldPriceCatalogTest extends ApiTestCase
{
    public function test_catalog_exposes_old_price_when_set(): void
    {
        if (! Schema::hasColumn('PRD_products', 'old_price')) {
            $this->markTestSkipped('Column old_price is missing — run migrations.');
        }

        $suffix = bin2hex(random_bytes(3));

        $category = PRD_Category::query()->create([
            'name' => 'OldPrice Cat '.$suffix,
            'slug' => 'old-price-cat-'.$suffix,
            'sort_order' => 9999,
            'is_active' => true,
            'is_accompanying' => false,
        ]);

        $product = PRD_Product::query()->create([
            'name' => 'OldPrice Product '.$suffix,
            'slug' => 'old-price-product-'.$suffix,
            'sku' => 'OLD-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 400,
            'old_price' => 550,
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

        $match = null;
        foreach ($response->json('categories') ?? [] as $node) {
            foreach ($node['items'] ?? [] as $item) {
                if ((int) ($item['id'] ?? 0) === (int) $product->id) {
                    $match = $item;
                    break 2;
                }
            }
        }

        $this->assertNotNull($match);
        $this->assertSame(400, (int) ($match['price']['amount'] ?? 0));
        $this->assertSame(550, (int) ($match['old_price']['amount'] ?? 0));
        $this->assertSame('RUB', $match['old_price']['currency'] ?? null);
    }

    public function test_catalog_old_price_null_when_absent(): void
    {
        if (! Schema::hasColumn('PRD_products', 'old_price')) {
            $this->markTestSkipped('Column old_price is missing — run migrations.');
        }

        $suffix = bin2hex(random_bytes(3));

        $category = PRD_Category::query()->create([
            'name' => 'NoOld Cat '.$suffix,
            'slug' => 'no-old-cat-'.$suffix,
            'sort_order' => 9998,
            'is_active' => true,
            'is_accompanying' => false,
        ]);

        $product = PRD_Product::query()->create([
            'name' => 'NoOld Product '.$suffix,
            'slug' => 'no-old-product-'.$suffix,
            'sku' => 'NOOLD-'.$suffix,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 300,
            'old_price' => null,
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

        $match = null;
        foreach ($response->json('categories') ?? [] as $node) {
            foreach ($node['items'] ?? [] as $item) {
                if ((int) ($item['id'] ?? 0) === (int) $product->id) {
                    $match = $item;
                    break 2;
                }
            }
        }

        $this->assertNotNull($match);
        $this->assertArrayHasKey('old_price', $match);
        $this->assertNull($match['old_price']);
    }
}
