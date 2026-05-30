<?php

namespace App\Filament\Company\Widgets;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Legal\Command\UpdateAdminCompanyLegalUseCase;
use App\Application\Company\Legal\Query\GetAdminCompanyLegalQuery;
use App\Filament\Company\Concerns\InteractsWithCompanySettingsForm;
use App\Filament\Company\Schemas\CompanyLegalSettingsForm;
use App\Filament\Company\Support\FilamentCompanyLegalFormMapper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Widgets\Widget;

class HubCompanyLegalPanel extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithCompanySettingsForm;
    use InteractsWithSchemas;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = true;

    protected string $view = 'filament.operations.widgets.settings-form-panel';

    protected int|string|array $columnSpan = 'full';

    protected function getSettingsFormElementId(): string
    {
        return 'company-legal-form';
    }

    protected function loadSettingsState(): array
    {
        return FilamentCompanyLegalFormMapper::toFormState(
            app(GetAdminCompanyLegalQuery::class)->execute(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return CompanyLegalSettingsForm::configure($schema);
    }

    protected function persistSettings(array $data): void
    {
        try {
            app(UpdateAdminCompanyLegalUseCase::class)->execute(
                FilamentCompanyLegalFormMapper::toDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }
    }
}
