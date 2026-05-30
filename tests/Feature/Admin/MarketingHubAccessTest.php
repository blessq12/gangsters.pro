<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class MarketingHubAccessTest extends TestCase
{
    public function test_guest_marketing_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get('/admin/marketing')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_marketing_hub_is_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/marketing')
            ->assertOk();
    }

    public function test_marketing_hub_tabs_are_ok(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->superAdmin()->create();

        foreach (['banners', 'promotions'] as $tab) {
            $this->actingAs($user)
                ->get('/admin/marketing?tab='.$tab)
                ->assertOk();
        }
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
