<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings;

use App\Filament\Resources\ShoppingCartRuleSettings\Pages\EditShoppingCartRuleSetting;
use App\Filament\Resources\ShoppingCartRuleSettings\Pages\ListShoppingCartRuleSettings;
use App\Filament\Resources\ShoppingCartRuleSettings\Schemas\ShoppingCartRuleSettingForm;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartRuleSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ShoppingCartRuleSettingResource extends Resource
{
    protected static ?string $model = SHP_ShoppingCartRuleSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Правила корзины';

    protected static ?string $modelLabel = 'Настройки правил корзины';

    protected static ?string $pluralModelLabel = 'Правила корзины';

    protected static string|UnitEnum|null $navigationGroup = 'Заказы';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return ShoppingCartRuleSettingForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoppingCartRuleSettings::route('/'),
            'edit' => EditShoppingCartRuleSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => static::resolveSettingsRecord()]);
    }

    public static function resolveSettingsRecord(): SHP_ShoppingCartRuleSetting
    {
        return SHP_ShoppingCartRuleSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'complement_rule_enabled' => true,
                'gift_rule_enabled' => true,
                'gift_threshold_kopecks' => 180_000,
                'rolls_per_complement' => 2,
                'complement_rule_sort' => 10,
                'gift_rule_sort' => 20,
            ],
        );
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
