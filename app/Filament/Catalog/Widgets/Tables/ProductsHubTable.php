<?php

namespace App\Filament\Catalog\Widgets\Tables;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Filament\Catalog\Support\CatalogHubTableActions;
use App\Filament\Catalog\Support\CatalogHubTablePresentation;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProductsHubTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return CatalogHubTableActions::forProducts(
            $table
                ->query(
                    PRD_Product::query()
                        ->where('catalog_kind', CatalogItemKind::Product->value)
                        ->orderBy('name'),
                )
                ->heading('Товары')
                ->columns([
                    TextColumn::make('name')
                        ->label('Название')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('slug')
                        ->label('Слаг')
                        ->searchable(),
                    CatalogHubTablePresentation::catalogItemSkuColumn(),
                    TextColumn::make('price')
                        ->label('Цена, ₽')
                        ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : number_format($state, 0, ',', ' ').' ₽')
                        ->sortable(),
                    CatalogHubTablePresentation::catalogItemStatusColumn(),
                    CatalogHubTablePresentation::productSystemColumn(),
                ])
                ->filters([
                    CatalogHubTablePresentation::catalogItemStatusFilter(),
                    CatalogHubTablePresentation::productSystemFilter(),
                    CatalogHubTablePresentation::productMetaFilter(),
                ])
                ->defaultSort('name'),
        );
    }
}
