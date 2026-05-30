<?php

namespace App\Filament\Catalog\Resources;

use App\Filament\Catalog\Support\RedirectsCatalogIndexToHub;
use App\Filament\Catalog\Resources\TagResource\Pages\CreateTag;
use App\Filament\Catalog\Resources\TagResource\Pages\EditTag;
use App\Filament\Catalog\Resources\TagResource\Schemas\TagForm;
use App\Infrastructure\Product\Model\PRD_Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TagResource extends Resource
{
    use RedirectsCatalogIndexToHub;

    protected static string $catalogHubTab = 'tags';

    protected static ?string $model = PRD_Tag::class;

    protected static ?string $slug = 'catalog/tags';

    protected static ?string $modelLabel = 'тег';

    protected static ?string $pluralModelLabel = 'теги';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
