<?php

namespace App\Filament\Order\Resources;

use App\Filament\Order\Resources\Pages\ListOrders;
use App\Filament\Order\Resources\Pages\ViewOrder;
use App\Filament\Support\AdminNavigationGroup;
use App\Filament\Order\Resources\Schemas\OrderViewSchema;
use App\Filament\Order\Resources\Tables\OrdersTable;
use App\Infrastructure\Order\Model\ORD_Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = ORD_Order::class;

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $slug = 'orders';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 10;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Sales;

    public static function form(Schema $schema): Schema
    {
        return OrderViewSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
