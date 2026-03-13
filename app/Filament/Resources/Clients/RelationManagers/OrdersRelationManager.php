<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID заказа')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('total_price')
                    ->label('Сумма')
                    ->money('RUB'),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Заглушка: новые заказы создаются не через админку клиента.
            ])
            ->recordActions([
                // Можно будет добавить просмотр/переход в OrderResource позже.
            ]);
    }
}

