<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings;

use App\Filament\Resources\ShoppingCartRuleSettings\Pages\EditShoppingCartRuleSetting;
use App\Filament\Resources\ShoppingCartRuleSettings\Pages\ListShoppingCartRuleSettings;
use App\Filament\Resources\ShoppingCartRuleSettings\Schemas\ShoppingCartRuleSettingForm;
use App\Filament\Resources\ShoppingCartRuleSettings\Tables\ShoppingCartRuleSettingsTable;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartRuleSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ShoppingCartRuleSettingResource extends Resource
{
    protected static ?string $model = SHP_ShoppingCartRuleSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Правила корзины';

    protected static ?string $modelLabel = 'Настройки правил корзины';

    protected static ?string $pluralModelLabel = 'Правила корзины';

    protected static string|UnitEnum|null $navigationGroup = 'Магазин';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ShoppingCartRuleSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppingCartRuleSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoppingCartRuleSettings::route('/'),
            'edit' => EditShoppingCartRuleSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
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
