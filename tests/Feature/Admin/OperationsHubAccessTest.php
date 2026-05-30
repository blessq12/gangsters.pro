<?php

namespace Tests\Feature\Admin;

use App\Filament\Catalog\Tables\HubProductsTable;
use App\Filament\Operations\Tables\HubClientsTable;
use App\Filament\Operations\Widgets\HubCartRulesPanel;
use App\Filament\Operations\Widgets\HubDeliveryZonePanel;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
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

    public function test_legacy_delivery_zone_page_is_not_registered(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/operations/delivery-zone')
            ->assertNotFound();
    }

    public function test_legacy_cart_rules_page_is_not_registered(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/operations/cart-rules')
            ->assertNotFound();
    }

    public function test_hub_livewire_components_are_registered(): void
    {
        $this->assertHubLivewireAlias(
            'app.filament.operations.widgets.hub-delivery-zone-panel',
            HubDeliveryZonePanel::class,
        );
        $this->assertHubLivewireAlias(
            'app.filament.operations.widgets.hub-cart-rules-panel',
            HubCartRulesPanel::class,
        );
        $this->assertHubLivewireAlias(
            'app.filament.operations.tables.hub-clients-table',
            HubClientsTable::class,
        );
        $this->assertHubLivewireAlias(
            'app.filament.catalog.tables.hub-products-table',
            HubProductsTable::class,
        );
    }

    /**
     * @param  class-string  $expectedClass
     */
    private function assertHubLivewireAlias(string $alias, string $expectedClass): void
    {
        $component = Livewire::new($alias);

        $this->assertInstanceOf($expectedClass, $component);
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
