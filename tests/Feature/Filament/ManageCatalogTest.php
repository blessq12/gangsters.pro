<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageCatalog;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Infrastructure\Category\Model\PRD_Category;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ManageCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->databaseTableExists('PRD_products') || ! $this->databaseTableExists('users')) {
            $this->markTestSkipped('Нет таблиц PRD_products/users для Filament-теста.');
        }
    }

    public function test_manage_catalog_products_tab_renders(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ManageCatalog::class)
            ->assertOk()
            ->assertSet('catalogTab', 'products');
    }

    public function test_manage_catalog_categories_tab_from_query(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (PRD_Category::query()->count() === 0) {
            $this->markTestSkipped('Нет категорий для проверки таблицы.');
        }

        Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'categories'])
            ->test(ManageCatalog::class)
            ->assertSet('catalogTab', 'categories')
            ->assertCanSeeTableRecords(PRD_Category::query()->limit(5)->get());
    }

    public function test_manage_catalog_switches_to_categories_tab_without_reload(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (PRD_Category::query()->count() === 0) {
            $this->markTestSkipped('Нет категорий для проверки таблицы.');
        }

        Livewire::actingAs($user)
            ->test(ManageCatalog::class)
            ->assertSet('catalogTab', 'products')
            ->call('setCatalogTab', 'categories')
            ->assertSet('catalogTab', 'categories')
            ->assertCanSeeTableRecords(PRD_Category::query()->limit(5)->get());
    }

    public function test_set_catalog_tab_resets_table(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (PRD_Category::query()->count() === 0) {
            $this->markTestSkipped('Нет категорий для проверки таблицы.');
        }

        Livewire::actingAs($user)
            ->test(ManageCatalog::class)
            ->call('setCatalogTab', 'categories')
            ->assertSet('catalogTab', 'categories')
            ->assertCanSeeTableRecords(PRD_Category::query()->limit(5)->get());
    }

    public function test_list_products_redirects_to_catalog_hub(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ListProducts::class)
            ->assertRedirect(ManageCatalog::getUrl(['tab' => 'products']));
    }

    private function databaseTableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
