<?php

namespace App\Filament\Catalog\Resources;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Filament\Catalog\Resources\ProductSetResource\Pages\CreateProductSet;
use App\Filament\Catalog\Resources\ProductSetResource\Pages\EditProductSet;
use App\Filament\Catalog\Resources\ProductSetResource\Schemas\ProductSetForm;
use App\Infrastructure\Catalog\Model\PRD_Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductSetResource extends Resource
{
    protected static ?string $model = PRD_Product::class;

    protected static ?string $slug = 'catalog/sets';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Набор';

    protected static ?string $pluralModelLabel = 'Наборы';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ProductSetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('catalog_kind', CatalogItemKind::Set->value);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateProductSet::route('/create'),
            'edit' => EditProductSet::route('/{record}/edit'),
        ];
    }
}
