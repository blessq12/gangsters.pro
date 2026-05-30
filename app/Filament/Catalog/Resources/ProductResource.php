<?php

namespace App\Filament\Catalog\Resources;

use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Catalog\Support\RedirectsCatalogIndexToHub;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use App\Filament\Catalog\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Catalog\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Catalog\Resources\ProductResource\Schemas\ProductForm;
use App\Infrastructure\Product\Model\PRD_Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use AuthorizesAdminHub;
    use RedirectsCatalogIndexToHub;

    protected static string $catalogHubTab = 'products';

    protected static ?string $model = PRD_Product::class;

    protected static ?string $slug = 'catalog/products';

    protected static ?string $modelLabel = 'товар';

    protected static ?string $pluralModelLabel = 'товары';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static bool $shouldRegisterNavigation = false;

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Catalog;
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
