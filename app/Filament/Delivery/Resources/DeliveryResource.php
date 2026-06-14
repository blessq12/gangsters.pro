<?php

namespace App\Filament\Delivery\Resources;

use App\Filament\Delivery\Resources\DeliveryResource\Pages\ManageDelivery;
use App\Filament\Support\AdminNavigationGroup;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliveryResource extends Resource
{
    protected static ?string $model = DLV_Configuration::class;

    protected static ?string $navigationLabel = 'Доставка';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $slug = 'delivery';

    protected static ?string $modelLabel = 'Доставка';

    protected static ?string $pluralModelLabel = 'Доставка';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Service;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDelivery::route('/'),
        ];
    }
}
