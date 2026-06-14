<?php

namespace App\Filament\Catalog\Widgets\Tables;

use App\Filament\Catalog\Support\CatalogHubTableActions;
use App\Filament\Catalog\Support\CatalogHubTablePresentation;
use App\Infrastructure\Catalog\Model\PRD_Tag;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TagsHubTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return CatalogHubTableActions::forTags(
            $table
                ->query(PRD_Tag::query())
                ->heading('Теги')
                ->description('Перетаскивайте строки, чтобы изменить порядок тегов в каталоге.')
                ->defaultSort('sort_order')
                ->reorderable('sort_order')
                ->columns([
                    TextColumn::make('code')
                        ->label('Код')
                        ->searchable(),
                    TextColumn::make('label')
                        ->label('Подпись')
                        ->searchable(),
                    TextColumn::make('color')
                        ->label('Цвет')
                        ->badge(),
                    CatalogHubTablePresentation::tagStatusColumn(),
                ]),
        );
    }
}
