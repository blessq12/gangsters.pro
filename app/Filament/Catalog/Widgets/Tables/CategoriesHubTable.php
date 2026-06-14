<?php

namespace App\Filament\Catalog\Widgets\Tables;

use App\Filament\Catalog\Support\CatalogHubTableActions;
use App\Filament\Catalog\Support\CatalogHubTablePresentation;
use App\Infrastructure\Catalog\Model\PRD_Category;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CategoriesHubTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return CatalogHubTableActions::forCategories(
            $table
                ->query(PRD_Category::query()->withCount('categoryProducts'))
                ->heading('Категории')
                ->description('Перетаскивайте строки, чтобы изменить порядок категорий в каталоге.')
                ->defaultSort('sort_order')
                ->reorderable('sort_order')
                ->columns([
                    TextColumn::make('name')
                        ->label('Название')
                        ->searchable(),
                    TextColumn::make('slug')
                        ->label('Слаг')
                        ->searchable(),
                    TextColumn::make('category_products_count')
                        ->label('Позиций')
                        ->counts('categoryProducts'),
                    CatalogHubTablePresentation::categoryStatusColumn(),
                ])
                ->filters([
                    CatalogHubTablePresentation::categoryStatusFilter(),
                ]),
        );
    }
}
