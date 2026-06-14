<?php

namespace App\Filament\Catalog\Widgets\Tables;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Filament\Catalog\Support\CatalogHubTableActions;
use App\Filament\Catalog\Support\CatalogHubTablePresentation;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProductSetsHubTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return CatalogHubTableActions::forSets(
            $table
                ->query(
                    PRD_Product::query()
                        ->where('catalog_kind', CatalogItemKind::Set->value)
                        ->withCount('setLines')
                        ->orderBy('name'),
                )
                ->heading('Наборы')
                ->columns([
                    TextColumn::make('name')
                        ->label('Название')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('slug')
                        ->label('Слаг')
                        ->searchable(),
                    TextColumn::make('set_lines_count')
                        ->label('Позиций в наборе')
                        ->sortable(),
                    TextColumn::make('price')
                        ->label('Цена, ₽')
                        ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : number_format($state, 0, ',', ' ').' ₽')
                        ->sortable(),
                    CatalogHubTablePresentation::catalogItemStatusColumn(),
                ])
                ->filters([
                    CatalogHubTablePresentation::catalogItemStatusFilter(),
                ])
                ->defaultSort('name'),
        );
    }
}
