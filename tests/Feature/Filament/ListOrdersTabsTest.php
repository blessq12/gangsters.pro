<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ListOrdersTabsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->databaseTableExists('ORD_orders')) {
            $this->markTestSkipped('Нет таблицы ORD_orders для Filament-теста.');
        }
    }

    public function test_list_orders_default_tab_is_new(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ListOrders::class)
            ->assertOk()
            ->assertSet('activeTab', 'new');
    }

    public function test_list_orders_preparing_tab_from_query(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (ORD_Order::query()->where('status', 'preparing')->count() === 0) {
            $this->markTestSkipped('Нет заказов в статусе preparing.');
        }

        $orders = ORD_Order::query()
            ->where('status', 'preparing')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'preparing'])
            ->test(ListOrders::class)
            ->assertSet('activeTab', 'preparing')
            ->loadTable()
            ->assertCanSeeTableRecords($orders);
    }

    public function test_list_orders_switches_to_delivered_tab(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (ORD_Order::query()->where('status', 'delivered')->count() === 0) {
            $this->markTestSkipped('Нет заказов в статусе delivered.');
        }

        $orders = ORD_Order::query()
            ->where('status', 'delivered')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        Livewire::actingAs($user)
            ->test(ListOrders::class)
            ->assertSet('activeTab', 'new')
            ->set('activeTab', 'delivered')
            ->assertSet('activeTab', 'delivered')
            ->loadTable()
            ->assertCanSeeTableRecords($orders);
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
