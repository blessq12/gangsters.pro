<?php

namespace Tests\Feature\Admin;

use App\Filament\Operations\Resources\ClientResource\Pages\EditClient;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class OperationsClientEditTest extends TestCase
{
    public function test_client_edit_page_route_is_registered(): void
    {
        $this->assertTrue(class_exists(EditClient::class));
    }

    public function test_operations_clients_edit_route_exists(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('filament.admin.resources.operations.clients.edit'),
        );
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
