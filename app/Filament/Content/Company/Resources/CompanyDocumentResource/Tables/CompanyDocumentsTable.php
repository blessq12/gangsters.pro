<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Tables;

use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CompanyDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Слаг')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Документы не найдены')
            ->emptyStateDescription('Создайте юридический документ.')
            ->headerActions([
                Action::make('create')
                    ->label('Создать')
                    ->icon(Heroicon::Plus)
                    ->url(fn (): string => CompanyDocumentResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить'),
            ]);
    }
}
