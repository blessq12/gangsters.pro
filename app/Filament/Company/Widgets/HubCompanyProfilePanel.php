<?php

namespace App\Filament\Company\Widgets;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Contracts\CompanyLogoStoragePort;
use App\Application\Company\Profile\Command\UpdateAdminCompanyProfileUseCase;
use App\Filament\Support\AdminCompanyReadHelper;
use App\Filament\Company\Concerns\InteractsWithCompanySettingsForm;
use App\Filament\Company\Schemas\CompanyProfileSettingsForm;
use App\Filament\Company\Support\FilamentCompanyProfileFormMapper;
use App\Filament\Operations\Pages\ManageOperations;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Widgets\Widget;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HubCompanyProfilePanel extends Widget implements HasActions, HasSchemas
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
        return 'company-profile-form';
    }

    protected function loadSettingsState(): array
    {
        return FilamentCompanyProfileFormMapper::toFormState(
            app(AdminCompanyReadHelper::class)->profileState(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return CompanyProfileSettingsForm::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $upload = $data['logo_upload'] ?? null;
        if ($upload instanceof TemporaryUploadedFile) {
            $data['logo'] = app(CompanyLogoStoragePort::class)->store($upload);
        }

        unset($data['logo_upload']);

        return $data;
    }

    protected function persistSettings(array $data): void
    {
        try {
            app(UpdateAdminCompanyProfileUseCase::class)->execute(
                FilamentCompanyProfileFormMapper::toDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('delivery_zone')
                ->label('Доставка')
                ->url(ManageOperations::getUrl(['tab' => 'delivery']))
                ->color('gray'),
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
