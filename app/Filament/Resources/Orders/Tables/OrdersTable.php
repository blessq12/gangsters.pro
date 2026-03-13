<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eatsId')
                    ->label('ID заказа (внешний)')
                    ->searchable(),
                TextColumn::make('restaurantId')
                    ->label('ID ресторана')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_id')
                    ->label('ID пользователя')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя клиента')
                    ->searchable(),
                TextColumn::make('tel')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('street')
                    ->label('Улица')
                    ->searchable(),
                TextColumn::make('house')
                    ->label('Дом')
                    ->searchable(),
                TextColumn::make('building')
                    ->label('Корпус')
                    ->searchable(),
                TextColumn::make('staircase')
                    ->label('Подъезд')
                    ->searchable(),
                TextColumn::make('floor')
                    ->label('Этаж')
                    ->searchable(),
                TextColumn::make('apartment')
                    ->label('Квартира')
                    ->searchable(),
                TextColumn::make('full_address')
                    ->label('Полный адрес')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->label('Широта')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label('Долгота')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deliveryDate')
                    ->label('Дата/время доставки')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deliveryType')
                    ->label('Тип доставки')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Сумма заказа')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('itemsCost')
                    ->label('Сумма товаров')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deliveryFee')
                    ->label('Доставка')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('change')
                    ->label('Сдача')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('delivery')
                    ->label('Доставка')
                    ->boolean(),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->searchable(),
                TextColumn::make('personQty')
                    ->label('Кол-во персон')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payType')
                    ->label('Тип оплаты')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frontpad_id')
                    ->label('ID во Frontpad')
                    ->searchable(),
                TextColumn::make('discriminator')
                    ->label('Дискриминатор')
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
