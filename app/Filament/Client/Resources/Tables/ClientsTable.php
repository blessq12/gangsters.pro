<?php

namespace App\Filament\Client\Resources\Tables;

use App\Filament\Client\Support\ClientProfileReader;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->formatStateUsing(fn (string $state): string => ClientProfileReader::formatPhone($state))
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('addresses_count')
                    ->label('Адресов')
                    ->counts('addresses')
                    ->sortable(),
                TextColumn::make('consent_marketing')
                    ->label('Маркетинг')
                    ->formatStateUsing(fn (bool $state): string => ClientProfileReader::boolLabel($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Зарегистрирован')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('Клиенты не найдены')
            ->emptyStateDescription('Пока нет ни одного зарегистрированного клиента.');
    }
}
