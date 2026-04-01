<?php

namespace App\Filament\Resources\ProductTags;

use App\Filament\Resources\ProductTags\Pages\CreateProductTag;
use App\Filament\Resources\ProductTags\Pages\EditProductTag;
use App\Filament\Resources\ProductTags\Pages\ListProductTags;
use App\Filament\Resources\ProductTags\Schemas\ProductTagForm;
use App\Filament\Resources\ProductTags\Tables\ProductTagsTable;
use App\Infrastructure\Product\Model\PRD_Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductTagResource extends Resource
{
    protected static ?string $model = PRD_Tag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Теги товаров';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог и товары';

    protected static ?int $navigationSort = 21;

    public static function getModelLabel(): string
    {
        return 'Тег товара';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Теги товаров';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductTagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductTags::route('/'),
            'create' => CreateProductTag::route('/create'),
            'edit' => EditProductTag::route('/{record}/edit'),
        ];
    }
}
