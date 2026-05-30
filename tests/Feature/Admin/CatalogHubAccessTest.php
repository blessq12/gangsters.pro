<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CatalogHubAccessTest extends TestCase
{
    public function test_guest_catalog_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin/catalog')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_catalog_hub_is_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/catalog')
            ->assertOk();
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
