<?php

namespace Tests\Feature\Admin;

use App\Filament\Operations\Tables\HubOrdersTable;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

final class OperationsHubTablePaginationRenderTest extends TestCase
{
    public function test_hub_orders_table_returns_length_aware_paginator(): void
    {
        $component = Livewire::test(HubOrdersTable::class);

        $records = $component->instance()->getTableRecords();

        $this->assertInstanceOf(LengthAwarePaginator::class, $records);
        $this->assertSame(0, $records->onEachSide);

        if ($records->total() > $records->perPage()) {
            $this->assertTrue($records->hasPages());
        }
    }

    public function test_hub_paginator_window_is_compact_on_early_pages(): void
    {
        $paginator = (new LengthAwarePaginator([], 9188, 25, 3))->onEachSide(0);

        $this->assertLessThanOrEqual(
            7,
            $this->countPaginatorPageNumbers($paginator),
            'Early-page window must not render 10+ consecutive page numbers.',
        );
    }

    public function test_operations_orders_tab_renders_full_pagination_markup(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)
            ->get('/admin/operations?tab=orders');

        $response->assertOk();
        $response->assertSee('fi-pagination-items', false);
        $response->assertSee('fi-pagination-overview', false);
        $response->assertSee('hub-table-pagination.css', false);
        $response->assertDontSee('fi-pagination fi-simple', false);
    }

    private function countPaginatorPageNumbers(LengthAwarePaginator $paginator): int
    {
        $count = 0;

        foreach ($paginator->render()->offsetGet('elements') as $element) {
            if (is_array($element)) {
                $count += count($element);
            }
        }

        return $count;
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Users table is not available.');
        }
    }
}
