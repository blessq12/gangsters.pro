<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ShoppingCartRuleSettings\Pages\ListShoppingCartRuleSettings;
use App\Filament\Resources\ShoppingCartRuleSettings\ShoppingCartRuleSettingResource;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartRuleSetting;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ShoppingCartRuleSettingNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->databaseTableExists('SHP_shopping_cart_rule_settings')) {
            $this->markTestSkipped('Нет таблицы SHP_shopping_cart_rule_settings для Filament-теста.');
        }
    }

    public function test_resource_navigation_group_is_orders(): void
    {
        $this->assertSame('Заказы', ShoppingCartRuleSettingResource::getNavigationGroup());
    }

    public function test_navigation_url_points_to_edit(): void
    {
        $settings = ShoppingCartRuleSettingResource::resolveSettingsRecord();

        $this->assertSame(
            ShoppingCartRuleSettingResource::getUrl('edit', ['record' => $settings]),
            ShoppingCartRuleSettingResource::getNavigationUrl(),
        );
    }

    public function test_list_redirects_to_edit(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        $settings = SHP_ShoppingCartRuleSetting::query()->find(1)
            ?? ShoppingCartRuleSettingResource::resolveSettingsRecord();

        Livewire::actingAs($user)
            ->test(ListShoppingCartRuleSettings::class)
            ->assertRedirect(ShoppingCartRuleSettingResource::getUrl('edit', ['record' => $settings]));
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
