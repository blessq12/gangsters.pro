<?php

namespace App\Filament\Operations\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\Command\UpdateDeliveryZoneUseCase;
use App\Application\Operations\Delivery\Query\GetAdminDeliveryZoneQuery;
use App\Filament\Forms\Components\YandexDeliveryZoneMap;
use App\Filament\Operations\Pages\Concerns\InteractsWithOperationsSettingsForm;
use App\Filament\Operations\Support\FilamentDeliveryZoneFormMapper;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;

class ManageDeliveryZone extends Page
{
    use InteractsWithOperationsSettingsForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $navigationLabel = 'Зона доставки';

    protected static ?string $title = 'Зона доставки';

    protected static ?string $slug = 'operations/delivery-zone';

    protected static bool $shouldRegisterNavigation = false;

    protected function loadSettingsState(): array
    {
        return FilamentDeliveryZoneFormMapper::toFormState(
            app(GetAdminDeliveryZoneQuery::class)->execute(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Компания')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Компания')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Карта')
                    ->schema([
                        Hidden::make('delivery_zone_geojson'),
                        YandexDeliveryZoneMap::make('delivery_zone_map')
                            ->label('Зона на карте')
                            ->dehydrated(false),
                    ]),
                Section::make('Координаты кухни')
                    ->schema([
                        TextInput::make('kitchen_latitude')
                            ->label('Широта')
                            ->numeric(),
                        TextInput::make('kitchen_longitude')
                            ->label('Долгота')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
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
