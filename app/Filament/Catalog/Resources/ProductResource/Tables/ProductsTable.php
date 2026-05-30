<?php

namespace App\Filament\Catalog\Resources\ProductResource\Tables;

use App\Support\Money;
use App\Support\Product\ProductStatusLabels;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ProductsTable
{
    /**
     * @return array<int, \Filament\Tables\Columns\Column>
     */
    public static function listColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Название')
                ->searchable(),
            TextColumn::make('articul')
                ->label('Артикул')
                ->searchable(),
            TextColumn::make('price_rubles')
                ->label('Цена')
                ->formatStateUsing(fn ($state): string => $state !== null && $state !== ''
                    ? Money::formatRublesRuAdaptive((float) $state).' ₽'
                    : '—'),
            TextColumn::make('status')
                ->label('Статус')
                ->badge()
                ->formatStateUsing(fn (string $state): string => ProductStatusLabels::label($state))
                ->color(fn (string $state): string => ProductStatusLabels::color($state)),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns(static::listColumns())
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ProductStatusLabels::options()),
            ])
            ->defaultSort('name');
    }
}
