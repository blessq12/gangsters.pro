<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Tables;

use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CompanyDocumentsTable
{
    public static function configure(Table $table): Table
    {
        $labels = CompanyDocumentResource::documentDefinitions();

        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('key')
                    ->label('Тип')
                    ->formatStateUsing(
                        fn (?string $state): string => $labels[$state] ?? (string) $state,
                    )
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Документы не найдены')
            ->emptyStateDescription('Документы появятся после инициализации компании.')
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать'),
            ]);
    }
}
