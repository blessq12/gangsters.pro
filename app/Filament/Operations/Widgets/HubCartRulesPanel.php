<?php

namespace App\Filament\Operations\Widgets;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\CartRules\Command\UpdateCartRuleSettingsUseCase;
use App\Filament\Support\AdminCartRuleSettingsReadHelper;
use App\Filament\Operations\Concerns\InteractsWithOperationsSettingsForm;
use App\Filament\Operations\Schemas\CartRuleSettingsForm;
use App\Filament\Operations\Support\FilamentCartRuleSettingsFormMapper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Widgets\Widget;

class HubCartRulesPanel extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithOperationsSettingsForm;
    use InteractsWithSchemas;

    protected static bool $isDiscovered = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.operations.widgets.settings-form-panel';

    protected int|string|array $columnSpan = 'full';

    protected function getSettingsFormElementId(): string
    {
        return 'operations-cart-rules-form';
    }

    protected function loadSettingsState(): array
    {
        return FilamentCartRuleSettingsFormMapper::toFormState(
            app(AdminCartRuleSettingsReadHelper::class)->settingsState(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return CartRuleSettingsForm::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function persistSettings(array $data): void
    {
        try {
            app(UpdateCartRuleSettingsUseCase::class)->execute(
                FilamentCartRuleSettingsFormMapper::toDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }
    }
}
