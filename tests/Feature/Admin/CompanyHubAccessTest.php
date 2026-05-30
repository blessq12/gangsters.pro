<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CompanyHubAccessTest extends TestCase
{
    public function test_guest_company_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin/company')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_company_hub_is_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/company')
            ->assertOk();
    }

    public function test_company_hub_tabs_are_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        foreach (['profile', 'legal', 'documents', 'staff'] as $tab) {
            $this->actingAs($user)
                ->get('/admin/company?tab='.$tab)
                ->assertOk();
        }
    }

    public function test_legacy_companies_index_is_not_registered(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/companies')
            ->assertNotFound();
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
