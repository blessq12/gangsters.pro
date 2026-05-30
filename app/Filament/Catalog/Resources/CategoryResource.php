<?php

namespace App\Filament\Catalog\Resources;

use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Catalog\Support\RedirectsCatalogIndexToHub;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use App\Filament\Catalog\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Catalog\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Catalog\Resources\CategoryResource\Schemas\CategoryForm;
use App\Infrastructure\Category\Model\PRD_Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use AuthorizesAdminHub;
    use RedirectsCatalogIndexToHub;

    protected static string $catalogHubTab = 'categories';

    protected static ?string $model = PRD_Category::class;

    protected static ?string $slug = 'catalog/categories';

    protected static ?string $modelLabel = 'категория';

    protected static ?string $pluralModelLabel = 'категории';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static bool $shouldRegisterNavigation = false;

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Catalog;
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
