<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EmptyFilamentPanelTest extends TestCase
{
    public function test_guest_admin_home_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_home_redirects_to_dashboard(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/admin/dashboard');
    }

    public function test_authenticated_admin_dashboard_is_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
