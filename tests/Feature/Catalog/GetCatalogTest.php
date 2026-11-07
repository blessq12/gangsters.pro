<?php

namespace Tests\Feature\Catalog;

use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Illuminate\Support\Facades\DB;
use Tests\ApiTestCase;

final class GetCatalogTest extends ApiTestCase
{
    public function test_active_accompanying_category_is_in_catalog_and_upsell(): void
    {
        $suffix = bin2hex(random_bytes(3));

        $accompanying = $this->seedCatalogCategory(
            name: 'Сопутствующая '.$suffix,
            slug: 'accompanying-'.$suffix,
            sortOrder: 9998,
            isAccompanying: true,
        );
        $menuOnly = $this->seedCatalogCategory(
            name: 'Меню '.$suffix,
            slug: 'menu-only-'.$suffix,
            sortOrder: 9997,
            isAccompanying: false,
        );

        $this->seedCatalogProductInCategory($accompanying, 'Сопутствующий товар '.$suffix, 'acc-product-'.$suffix);
        $this->seedCatalogProductInCategory($menuOnly, 'Товар меню '.$suffix, 'menu-product-'.$suffix);

        $response = $this->getJson('/api/catalog');
        $response->assertOk();

        $menuIds = $this->categoryIds($response->json('categories') ?? []);
        $upsellIds = $this->categoryIds($response->json('accompanying_categories') ?? []);

        $this->assertContains($accompanying->id, $menuIds);
        $this->assertContains($accompanying->id, $upsellIds);
        $this->assertContains($menuOnly->id, $menuIds);
        $this->assertNotContains($menuOnly->id, $upsellIds);
    }

    public function test_catalog_returns_storefront_snapshot(): void
    {
        $response = $this->getJson('/api/catalog');

        $response->assertOk()
            ->assertJsonStructure([
                'categories',
                'accompanying_categories',
                'complement_products',
            ]);

        $this->assertNotEmpty($response->json('categories'));

        $firstCategory = $response->json('categories.0');
        $this->assertArrayHasKey('category', $firstCategory);
        $this->assertArrayHasKey('items', $firstCategory);
        $this->assertArrayHasKey('id', $firstCategory['category']);
        $this->assertArrayHasKey('name', $firstCategory['category']);
    }

    private function seedCatalogCategory(
        string $name,
        string $slug,
        int $sortOrder,
        bool $isAccompanying,
    ): PRD_Category {
        return PRD_Category::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'is_accompanying' => $isAccompanying,
        ]);
    }

    private function seedCatalogProductInCategory(PRD_Category $category, string $name, string $slug): void
    {
        $product = PRD_Product::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sku' => 'SKU-'.$slug,
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => false,
            'price' => 100,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        DB::table('PRD_category_product')->insert([
            'category_id' => $category->id,
            'product_id' => $product->id,
            'sort_order' => 0,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $nodes
     * @return list<int>
     */
    private function categoryIds(array $nodes): array
    {
        $ids = [];

        foreach ($nodes as $node) {
            $id = $node['category']['id'] ?? null;
            if (is_numeric($id)) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}
