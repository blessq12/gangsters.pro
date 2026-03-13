<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('street')
                    ->label('Улица')
                    ->searchable(),
                TextColumn::make('house')
                    ->label('Дом')
                    ->searchable(),
                TextColumn::make('apartment')
                    ->label('Квартира')
                    ->searchable(),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(80),
                IconColumn::make('deleted_at')
                    ->label('Удалён')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->deleted_at),
                IconColumn::make('id')
                    ->label('По умолчанию')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        /** @var \App\Infrastructure\Client\Model\UR_ClientAddress $record */
                        $client = $record->client;

                        return $client?->default_address_id === $record->id;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Заглушка: редактирование/создание адресов делаем через фронт и API.
            ])
            ->recordActions([
                // Позже можно добавить просмотр/редактирование, если понадобится.
            ]);
    }
}

