<?php

namespace App\Filament\Operations\Pages;

use App\Filament\Operations\Tables\HubClientsTable;
use App\Filament\Operations\Tables\HubOrdersTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageOperations extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Операции';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Операции';

    protected static ?string $slug = 'operations';

    public function getHeading(): string
    {
        return 'Операции';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('operationsTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Заказы')
                            ->id('orders')
                            ->schema([
                                Livewire::make(HubOrdersTable::class),
                            ]),
                        Tab::make('Клиенты')
                            ->id('clients')
                            ->schema([
                                Livewire::make(HubClientsTable::class),
                            ]),
                        Tab::make('Зона доставки')
                            ->id('delivery')
                            ->schema([
                                View::make('filament.operations.hub-settings-link')
                                    ->viewData([
                                        'label' => 'Редактировать зону доставки',
                                        'url' => ManageDeliveryZone::getUrl(),
                                    ]),
                            ]),
                        Tab::make('Правила корзины')
                            ->id('cart-rules')
                            ->schema([
                                View::make('filament.operations.hub-settings-link')
                                    ->viewData([
                                        'label' => 'Настройки правил корзины',
                                        'url' => ManageCartRuleSettings::getUrl(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
