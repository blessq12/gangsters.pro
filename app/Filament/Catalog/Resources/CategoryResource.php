<?php

namespace App\Filament\Catalog\Resources;

use App\Filament\Catalog\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Catalog\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Catalog\Resources\CategoryResource\Schemas\CategoryForm;
use App\Infrastructure\Catalog\Model\PRD_Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = PRD_Category::class;

    protected static ?string $slug = 'catalog/categories';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Категория';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedFolder;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
