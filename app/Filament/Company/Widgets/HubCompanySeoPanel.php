<?php

namespace App\Filament\Company\Widgets;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Site\Command\UpdateAdminSiteSeoSettingsUseCase;
use App\Filament\Support\AdminSiteSeoReadHelper;
use App\Filament\Company\Concerns\InteractsWithCompanySettingsForm;
use App\Filament\Company\Schemas\SiteSeoSettingsForm;
use App\Filament\Company\Support\FilamentSiteSeoFormMapper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Widgets\Widget;

class HubCompanySeoPanel extends Widget implements HasActions, HasSchemas
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
        return 'company-seo-form';
    }

    protected function loadSettingsState(): array
    {
        return FilamentSiteSeoFormMapper::toFormState(
            app(AdminSiteSeoReadHelper::class)->settingsState(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return SiteSeoSettingsForm::configure($schema);
    }

    protected function persistSettings(array $data): void
    {
        try {
            app(UpdateAdminSiteSeoSettingsUseCase::class)->execute(
                FilamentSiteSeoFormMapper::toDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }
    }
}
