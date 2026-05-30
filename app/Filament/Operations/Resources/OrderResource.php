<?php

namespace App\Filament\Operations\Resources;

use App\Filament\Operations\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Operations\Resources\OrderResource\Schemas\OrderForm;
use App\Filament\Operations\Support\RedirectsOperationsIndexToHub;
use App\Infrastructure\Order\Model\ORD_Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use RedirectsOperationsIndexToHub;

    protected static string $operationsHubTab = 'orders';

    protected static ?string $model = ORD_Order::class;

    protected static ?string $slug = 'operations/orders';

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'заказы';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
