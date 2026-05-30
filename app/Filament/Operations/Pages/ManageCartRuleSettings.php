<?php

namespace App\Filament\Operations\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\CartRules\Command\UpdateCartRuleSettingsUseCase;
use App\Application\Operations\CartRules\Query\GetAdminCartRuleSettingsQuery;
use App\Filament\Operations\Pages\Concerns\InteractsWithOperationsSettingsForm;
use App\Filament\Operations\Support\FilamentCartRuleSettingsFormMapper;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;

class ManageCartRuleSettings extends Page
{
    use InteractsWithOperationsSettingsForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Правила корзины';

    protected static ?string $title = 'Правила корзины';

    protected static ?string $slug = 'operations/cart-rules';

    protected static bool $shouldRegisterNavigation = false;

    protected function loadSettingsState(): array
    {
        return FilamentCartRuleSettingsFormMapper::toFormState(
            app(GetAdminCartRuleSettingsQuery::class)->execute(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Правила')
                    ->schema([
                        Toggle::make('complement_rule_enabled')
                            ->label('Правило комплекта'),
                        Toggle::make('gift_rule_enabled')
                            ->label('Правило подарка'),
                        TextInput::make('gift_threshold_rubles')
                            ->label('Порог подарка, ₽')
                            ->numeric()
                            ->required(),
                        TextInput::make('rolls_per_complement')
                            ->label('Роллов на комплект')
                            ->numeric()
                            ->integer()
                            ->required(),
                        TextInput::make('complement_rule_sort')
                            ->label('Сортировка комплекта')
                            ->numeric()
                            ->integer()
                            ->required(),
                        TextInput::make('gift_rule_sort')
                            ->label('Сортировка подарка')
                            ->numeric()
                            ->integer()
                            ->required(),
                    ]),
            ]);
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
