<?php

namespace App\Filament\Resources\ComplimentaryItemRules;

use App\Filament\Resources\ComplimentaryItemRules\Pages\CreateComplimentaryItemRule;
use App\Filament\Resources\ComplimentaryItemRules\Pages\EditComplimentaryItemRule;
use App\Filament\Resources\ComplimentaryItemRules\Pages\ListComplimentaryItemRules;
use App\Filament\Resources\ComplimentaryItemRules\Schemas\ComplimentaryItemRuleForm;
use App\Filament\Resources\ComplimentaryItemRules\Tables\ComplimentaryItemRulesTable;
use App\Infrastructure\Order\Model\ComplimentaryItemRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ComplimentaryItemRuleResource extends Resource
{
    protected static ?string $model = ComplimentaryItemRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Сопутствующие товары';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог и товары';

    protected static ?int $navigationSort = 21;

    public static function getModelLabel(): string
    {
        return 'Правило сопутствующего товара';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Правила сопутствующих товаров';
    }

    public static function form(Schema $schema): Schema
    {
        return ComplimentaryItemRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplimentaryItemRulesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplimentaryItemRules::route('/'),
            'create' => CreateComplimentaryItemRule::route('/create'),
            'edit' => EditComplimentaryItemRule::route('/{record}/edit'),
        ];
    }
}
