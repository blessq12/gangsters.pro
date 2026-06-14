<?php

namespace App\Filament\Promotion\Resources;

use App\Filament\Promotion\Resources\PromotionPolicyResource\Pages\ManagePromotionPolicy;
use App\Infrastructure\Promotion\Model\PRM_Configuration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromotionPolicyResource extends Resource
{
    protected static ?string $model = PRM_Configuration::class;

    protected static ?string $navigationLabel = 'Правила акций';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $slug = 'promotion';

    protected static ?string $modelLabel = 'Правила акций';

    protected static ?string $pluralModelLabel = 'Правила акций';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 21;

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
            'index' => ManagePromotionPolicy::route('/'),
        ];
    }
}
