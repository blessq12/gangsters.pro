<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageUsers;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Infrastructure\Client\Model\UR_Client;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ManageUsersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->databaseTableExists('UR_clients') || ! $this->databaseTableExists('users')) {
            $this->markTestSkipped('Нет таблиц UR_clients/users для Filament-теста.');
        }
    }

    public function test_manage_users_clients_tab_renders(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ManageUsers::class)
            ->assertOk()
            ->assertSet('usersTab', 'clients');
    }

    public function test_manage_users_admins_tab_from_query(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'admins'])
            ->test(ManageUsers::class)
            ->assertSet('usersTab', 'admins')
            ->loadTable()
            ->assertCanSeeTableRecords(User::query()->limit(5)->get());
    }

    public function test_manage_users_switches_to_admins_tab_without_reload(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ManageUsers::class)
            ->assertSet('usersTab', 'clients')
            ->call('setUsersTab', 'admins')
            ->assertSet('usersTab', 'admins')
            ->loadTable()
            ->assertCanSeeTableRecords(User::query()->limit(5)->get());
    }

    public function test_set_users_tab_resets_table(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (UR_Client::query()->count() === 0) {
            $this->markTestSkipped('Нет клиентов для проверки таблицы.');
        }

        $clients = UR_Client::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        Livewire::actingAs($user)
            ->test(ManageUsers::class)
            ->call('setUsersTab', 'admins')
            ->call('setUsersTab', 'clients')
            ->assertSet('usersTab', 'clients')
            ->loadTable()
            ->assertCanSeeTableRecords($clients);
    }

    public function test_list_clients_redirects_to_users_hub(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ListClients::class)
            ->assertRedirect(ManageUsers::getUrl(['tab' => 'clients']));
    }

    public function test_list_users_redirects_to_users_hub(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ListUsers::class)
            ->assertRedirect(ManageUsers::getUrl(['tab' => 'admins']));
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
