<?php

namespace Tests\Feature\Admin;

use App\Filament\Marketing\Tables\HubBannersTable;
use App\Filament\Marketing\Tables\HubPromotionsTable;
use Livewire\Livewire;
use Tests\TestCase;

final class MarketingHubLivewireTest extends TestCase
{
    public function test_hub_livewire_components_are_registered(): void
    {
        $this->assertHubLivewireAlias(
            'app.filament.marketing.tables.hub-banners-table',
            HubBannersTable::class,
        );
        $this->assertHubLivewireAlias(
            'app.filament.marketing.tables.hub-promotions-table',
            HubPromotionsTable::class,
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
}
