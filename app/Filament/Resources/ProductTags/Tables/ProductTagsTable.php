<?php

namespace App\Filament\Resources\ProductTags\Tables;

use App\Filament\Resources\ProductTags\ProductTagResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductTagsTable
{
    /**
     * @param  class-string  $editResource
     */
    public static function configure(Table $table, string $editResource = ProductTagResource::class): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label('Бейдж')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Код')
                    ->searchable(),
                TextColumn::make('color')
                    ->label('Цвет'),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Активен')
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn ($record): string => $editResource::getUrl('edit', ['record' => $record])),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
