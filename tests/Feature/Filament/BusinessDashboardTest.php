<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\BusinessDashboard;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class BusinessDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->databaseTableExists('ORD_orders') || ! $this->databaseTableExists('users')) {
            $this->markTestSkipped('Нет таблиц для Filament business dashboard теста.');
        }
    }

    public function test_business_dashboard_renders_tabs(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(BusinessDashboard::class)
            ->assertOk()
            ->assertSee('Сводка')
            ->assertSee('Заказы')
            ->assertSee('Показатели');
    }

    public function test_business_dashboard_orders_tab_hides_top_products(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        $component = Livewire::actingAs($user)
            ->test(BusinessDashboard::class)
            ->set('metricsTab', 'orders')
            ->assertSet('metricsTab', 'orders');

        $widgetClasses = array_map(
            fn (string|object $widget): string => is_string($widget) ? $widget : $widget::class,
            $component->instance()->getWidgets(),
        );

        $this->assertContains(\App\Filament\Widgets\Business\OrdersPipelineStats::class, $widgetClasses);
        $this->assertNotContains(\App\Filament\Widgets\Business\TopProductsTable::class, $widgetClasses);
    }

    public function test_business_dashboard_accepts_period_filter(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(BusinessDashboard::class)
            ->set('filters', ['period' => '30d'])
            ->assertSet('filters.period', '30d');
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
