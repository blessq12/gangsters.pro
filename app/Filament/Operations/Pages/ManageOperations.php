<?php

namespace App\Filament\Operations\Pages;

use App\Filament\Operations\Tables\HubCartRulesProductsTable;
use App\Filament\Operations\Tables\HubClientsTable;
use App\Filament\Operations\Tables\HubOrdersTable;
use App\Filament\Operations\Widgets\HubCartRulesPanel;
use App\Filament\Operations\Widgets\HubDeliveryZonePanel;
use App\Filament\Operations\Tables\HubActiveCartsTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                        Tab::make('Доставка')
                            ->id('delivery')
                            ->schema([
                                Livewire::make(HubDeliveryZonePanel::class),
                            ]),
                        Tab::make('Активные корзины')
                            ->id('active-carts')
                            ->schema([
                                Livewire::make(HubActiveCartsTable::class),
                            ]),
                        Tab::make('Правила корзины')
                            ->id('cart-rules')
                            ->schema([
                                Livewire::make(HubCartRulesPanel::class),
                                Livewire::make(HubCartRulesProductsTable::class),
                            ]),
                    ]),
            ]);
    }
}
