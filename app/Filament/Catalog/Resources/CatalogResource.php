<?php

namespace App\Filament\Catalog\Resources;

use App\Filament\Catalog\Resources\CatalogResource\Pages\ManageCatalog;
use App\Filament\Support\AdminNavigationGroup;
use App\Infrastructure\Catalog\Model\PRD_Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CatalogResource extends Resource
{
    protected static ?string $model = PRD_Category::class;

    protected static ?string $navigationLabel = 'Каталог';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $slug = 'catalog';

    protected static ?string $modelLabel = 'Каталог';

    protected static ?string $pluralModelLabel = 'Каталог';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Storefront;

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
            'index' => ManageCatalog::route('/'),
        ];
    }
}
