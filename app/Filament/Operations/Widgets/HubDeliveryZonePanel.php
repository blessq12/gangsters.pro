<?php

namespace App\Filament\Operations\Widgets;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\Command\UpdateDeliveryZoneUseCase;
use App\Application\Operations\Delivery\Query\GetAdminDeliveryZoneQuery;
use App\Filament\Operations\Concerns\InteractsWithOperationsSettingsForm;
use App\Filament\Operations\Schemas\DeliveryZoneSettingsForm;
use App\Filament\Operations\Support\FilamentDeliveryZoneFormMapper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Widgets\Widget;

class HubDeliveryZonePanel extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithOperationsSettingsForm;
    use InteractsWithSchemas;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = true;

    protected string $view = 'filament.operations.widgets.settings-form-panel';

    protected int|string|array $columnSpan = 'full';

    protected function getSettingsFormElementId(): string
    {
        return 'operations-delivery-zone-form';
    }

    protected function loadSettingsState(): array
    {
        return FilamentDeliveryZoneFormMapper::toFormState(
            app(GetAdminDeliveryZoneQuery::class)->execute(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return DeliveryZoneSettingsForm::configure($schema);
    }

    protected function persistSettings(array $data): void
    {
        try {
            app(UpdateDeliveryZoneUseCase::class)->execute(
                FilamentDeliveryZoneFormMapper::toDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }
    }
}
