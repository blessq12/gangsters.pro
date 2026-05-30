<?php

namespace Tests\Feature\Admin;

use App\Filament\Operations\Tables\HubOrdersTable;
use Livewire\Livewire;
use Tests\TestCase;

final class OperationsHubOrdersTableTest extends TestCase
{
    public function test_hub_orders_table_is_registered(): void
    {
        $component = Livewire::new('app.filament.operations.tables.hub-orders-table');

        $this->assertInstanceOf(HubOrdersTable::class, $component);
    }
}
