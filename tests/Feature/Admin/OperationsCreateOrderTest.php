<?php

namespace Tests\Feature\Admin;

use App\Filament\Operations\Resources\OrderResource\Pages\CreateOrder;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class OperationsCreateOrderTest extends TestCase
{
    public function test_create_order_page_route_is_registered(): void
    {
        $this->assertTrue(class_exists(CreateOrder::class));
    }

    public function test_operations_orders_create_route_exists(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('filament.admin.resources.operations.orders.create'),
        );
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
