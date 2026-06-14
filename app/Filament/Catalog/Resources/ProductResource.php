<?php

namespace App\Filament\Catalog\Resources;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Filament\Catalog\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Catalog\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Catalog\Resources\ProductResource\Schemas\ProductForm;
use App\Infrastructure\Catalog\Model\PRD_Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = PRD_Product::class;

    protected static ?string $slug = 'catalog/products';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCube;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
