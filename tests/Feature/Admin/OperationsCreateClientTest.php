<?php

namespace Tests\Feature\Admin;

use App\Filament\Operations\Resources\ClientResource\Pages\CreateClient;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class OperationsCreateClientTest extends TestCase
{
    public function test_create_client_page_class_is_registered(): void
    {
        $this->assertTrue(class_exists(CreateClient::class));
    }

    public function test_operations_clients_create_route_exists(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('filament.admin.resources.operations.clients.create'),
        );
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
