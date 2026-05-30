<?php

namespace Tests\Feature\Admin;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Staff\Command\DeleteAdminUserUseCase;
use App\Filament\Company\Tables\HubDocumentsTable;
use App\Filament\Company\Tables\HubStaffTable;
use Livewire\Livewire;
use Tests\TestCase;

final class CompanyHubDeleteActionsTest extends TestCase
{
    public function test_hub_delete_table_widgets_are_registered(): void
    {
        $this->assertHubLivewireAlias(
            'app.filament.company.tables.hub-documents-table',
            HubDocumentsTable::class,
        );
        $this->assertHubLivewireAlias(
            'app.filament.company.tables.hub-staff-table',
            HubStaffTable::class,
        );
    }

    public function test_delete_admin_user_use_case_blocks_self_delete(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Нельзя удалить свою учётную запись.');

        app(DeleteAdminUserUseCase::class)->execute(1, 1);
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
