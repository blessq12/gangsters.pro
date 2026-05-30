<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AnalyticsDashboardAccessTest extends TestCase
{
    public function test_guest_dashboard_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin/dashboard')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_dashboard_is_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_dashboard_accepts_period_and_tab_query(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/dashboard?period=30d&tab=finance')
            ->assertOk();
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
