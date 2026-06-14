<?php

namespace App\Filament\Company\Resources\OperatorResource\Tables;

use App\Filament\Company\Resources\OperatorResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class OperatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tel')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Операторы не найдены')
            ->emptyStateDescription('Создайте первого оператора для доступа в панель.')
            ->headerActions([
                Action::make('create')
                    ->label('Создать')
                    ->icon(Heroicon::Plus)
                    ->url(fn (): string => OperatorResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Model $record): bool => OperatorResource::canDelete($record)),
            ]);
    }
}
