<?php

namespace App\Filament\Catalog\Resources;

use App\Filament\Catalog\Resources\TagResource\Pages\CreateTag;
use App\Filament\Catalog\Resources\TagResource\Pages\EditTag;
use App\Filament\Catalog\Resources\TagResource\Schemas\TagForm;
use App\Infrastructure\Catalog\Model\PRD_Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TagResource extends Resource
{
    protected static ?string $model = PRD_Tag::class;

    protected static ?string $slug = 'catalog/tags';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $modelLabel = 'Тег';

    protected static ?string $pluralModelLabel = 'Теги';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

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
