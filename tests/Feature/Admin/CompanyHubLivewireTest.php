<?php

namespace Tests\Feature\Admin;

use App\Filament\Company\Tables\HubDocumentsTable;
use App\Filament\Company\Widgets\HubCompanyProfilePanel;
use Livewire\Livewire;
use Tests\TestCase;

final class CompanyHubLivewireTest extends TestCase
{
    public function test_hub_livewire_components_are_registered(): void
    {
        $this->assertHubLivewireAlias(
            'app.filament.company.widgets.hub-company-profile-panel',
            HubCompanyProfilePanel::class,
        );
        $this->assertHubLivewireAlias(
            'app.filament.company.tables.hub-documents-table',
            HubDocumentsTable::class,
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
