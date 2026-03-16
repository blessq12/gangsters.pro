<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\ProductImagesRelationManager;
use App\Filament\Resources\Products\RelationManagers\ProductIngredientsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ProductTagsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ProductPricesRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Infrastructure\Product\Model\PRD_Product;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = PRD_Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Товары';

    // Группа: блок каталога и товаров
    protected static string|UnitEnum|null $navigationGroup = 'Каталог и товары';

    // Сортировка в навигации внутри блока каталога
    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return 'Товар';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Товары';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProductImagesRelationManager::class,
            ProductIngredientsRelationManager::class,
            ProductTagsRelationManager::class,
            ProductPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
