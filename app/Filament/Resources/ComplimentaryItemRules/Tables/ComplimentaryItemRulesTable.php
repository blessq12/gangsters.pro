<?php

namespace App\Filament\Resources\ComplimentaryItemRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class ComplimentaryItemRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('triggerCategories.name')
                    ->label('Триггер-категории')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(),
                TextColumn::make('giftProduct.name')
                    ->label('Бесплатный товар')
                    ->searchable(),
                TextColumn::make('priority')
                    ->label('Приоритет')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
