<?php

namespace Tests\Feature\Admin;

use App\Filament\Catalog\Tables\HubCategoriesTable;
use App\Filament\Catalog\Tables\HubProductsTable;
use App\Filament\Catalog\Tables\HubTagsTable;
use Livewire\Livewire;
use Tests\TestCase;

final class CatalogHubDeleteActionsTest extends TestCase
{
    public function test_catalog_hub_delete_tables_are_registered(): void
    {
        foreach ([
            'app.filament.catalog.tables.hub-products-table' => HubProductsTable::class,
            'app.filament.catalog.tables.hub-categories-table' => HubCategoriesTable::class,
            'app.filament.catalog.tables.hub-tags-table' => HubTagsTable::class,
        ] as $alias => $class) {
            $this->assertInstanceOf($class, Livewire::new($alias));
        }
    }
}
