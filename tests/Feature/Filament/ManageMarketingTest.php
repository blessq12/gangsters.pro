<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageMarketing;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Filament\Resources\Promotions\Pages\ListPromotions;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ManageMarketingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->databaseTableExists('banners') || ! $this->databaseTableExists('promotions')) {
            $this->markTestSkipped('Нет таблиц banners/promotions для Filament-теста.');
        }
    }

    public function test_manage_marketing_banners_tab_renders(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ManageMarketing::class)
            ->assertOk()
            ->assertSet('marketingTab', 'banners');
    }

    public function test_manage_marketing_promotions_tab_from_query(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'promotions'])
            ->test(ManageMarketing::class)
            ->assertSet('marketingTab', 'promotions')
            ->loadTable()
            ->assertCanSeeTableRecords(SYS_Promotion::query()->limit(5)->get());
    }

    public function test_manage_marketing_switches_to_promotions_tab_without_reload(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ManageMarketing::class)
            ->assertSet('marketingTab', 'banners')
            ->call('setMarketingTab', 'promotions')
            ->assertSet('marketingTab', 'promotions')
            ->loadTable()
            ->assertCanSeeTableRecords(SYS_Promotion::query()->limit(5)->get());
    }

    public function test_manage_marketing_banners_tab_shows_banners(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        if (SYS_Banner::query()->count() === 0) {
            $this->markTestSkipped('Нет баннеров для проверки таблицы.');
        }

        Livewire::actingAs($user)
            ->test(ManageMarketing::class)
            ->loadTable()
            ->assertCanSeeTableRecords(SYS_Banner::query()->limit(5)->get());
    }

    public function test_list_banners_redirects_to_marketing_hub(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ListBanners::class)
            ->assertRedirect(ManageMarketing::getUrl(['tab' => 'banners']));
    }

    public function test_list_promotions_redirects_to_marketing_hub(): void
    {
        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        Livewire::actingAs($user)
            ->test(ListPromotions::class)
            ->assertRedirect(ManageMarketing::getUrl(['tab' => 'promotions']));
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
