<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class OperationsHubAccessTest extends TestCase
{
    public function test_guest_operations_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin/operations')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_operations_hub_is_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/operations')
            ->assertOk();
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
