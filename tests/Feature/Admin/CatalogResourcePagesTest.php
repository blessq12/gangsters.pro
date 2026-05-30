<?php

namespace Tests\Feature\Admin;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_Tag;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class CatalogResourcePagesTest extends TestCase
{
    public function test_catalog_resource_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.resources.catalog.products.create'));
        $this->assertTrue(Route::has('filament.admin.resources.catalog.products.edit'));
        $this->assertTrue(Route::has('filament.admin.resources.catalog.categories.create'));
        $this->assertTrue(Route::has('filament.admin.resources.catalog.categories.edit'));
        $this->assertTrue(Route::has('filament.admin.resources.catalog.tags.create'));
        $this->assertTrue(Route::has('filament.admin.resources.catalog.tags.edit'));
    }

    public function test_guest_product_edit_redirects_to_login(): void
    {
        $this->skipUnlessCatalogTablesExist();

        $product = $this->createProduct();

        $this->get("/admin/catalog/products/{$product->id}/edit")
            ->assertRedirect('/admin/login');

        $product->delete();
    }

    public function test_guest_product_create_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin/catalog/products/create')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_product_create_does_not_throw_missing_index(): void
    {
        $this->skipUnlessCatalogTablesExist();

        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get('/admin/catalog/products/create');

        $this->assertFalse(
            str_contains((string) $response->baseResponse->getContent(), 'does not have an [index] page'),
            'Filament cancel URL must resolve via getIndexUrl(), not throw LogicException.',
        );

        if ($response->isOk()) {
            $response->assertOk();
        } else {
            $this->assertNotSame(500, $response->getStatusCode());
        }
    }

    public function test_authenticated_product_edit_does_not_return_server_error(): void
    {
        $this->skipIfFullEditRenderUnsafe();

        $this->skipUnlessCatalogTablesExist();

        $product = $this->createProduct();
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get("/admin/catalog/products/{$product->id}/edit");

        $this->assertEditResponseHealthy($response);

        $product->delete();
    }

    public function test_authenticated_category_edit_does_not_return_server_error(): void
    {
        $this->skipIfFullEditRenderUnsafe();
        $this->skipUnlessCategoryTablesExist();

        $category = PRD_Category::query()->create([
            'name' => 'Test category '.uniqid(),
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get("/admin/catalog/categories/{$category->id}/edit");

        $this->assertEditResponseHealthy($response);

        $category->delete();
    }

    public function test_authenticated_tag_edit_does_not_return_server_error(): void
    {
        $this->skipIfFullEditRenderUnsafe();
        $this->skipUnlessTagTablesExist();

        $tag = PRD_Tag::query()->create([
            'label' => 'Test tag '.uniqid(),
            'color' => 'amber',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get("/admin/catalog/tags/{$tag->id}/edit");

        $this->assertEditResponseHealthy($response);

        $tag->delete();
    }

    private function assertEditResponseHealthy(TestResponse $response): void
    {
        $this->assertFalse(
            str_contains((string) $response->baseResponse->getContent(), 'does not have an [index] page'),
        );

        $this->assertNotSame(500, $response->getStatusCode(), (string) $response->getContent());
        $this->assertNotSame(502, $response->getStatusCode());
        $this->assertNotSame(504, $response->getStatusCode());

        if ($response->isOk()) {
            $response->assertOk();
        }
    }

    private function skipIfFullEditRenderUnsafe(): void
    {
        if (! extension_loaded('xdebug')) {
            return;
        }

        $mode = getenv('XDEBUG_MODE') ?: ini_get('xdebug.mode') ?: '';

        if ($mode === '' || $mode === 'off') {
            return;
        }

        $this->markTestSkipped('Полный HTTP render Filament edit под активным Xdebug даёт segfault.');
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users`.');
        }
    }

    private function skipUnlessCatalogTablesExist(): void
    {
        $this->skipUnlessUsersTableExists();

        if (! Schema::hasTable('PRD_products')) {
            $this->markTestSkipped('Нет таблицы `PRD_products`.');
        }
    }

    private function skipUnlessCategoryTablesExist(): void
    {
        $this->skipUnlessUsersTableExists();

        if (! Schema::hasTable('PRD_categories')) {
            $this->markTestSkipped('Нет таблицы `PRD_categories`.');
        }
    }

    private function skipUnlessTagTablesExist(): void
    {
        $this->skipUnlessUsersTableExists();

        if (! Schema::hasTable('PRD_tags')) {
            $this->markTestSkipped('Нет таблицы `PRD_tags`.');
        }
    }

    private function createProduct(): PRD_Product
    {
        return PRD_Product::query()->create([
            'name' => 'Filament test product '.uniqid(),
            'status' => ProductEntity::STATUS_ACTIVE,
        ]);
    }
}
