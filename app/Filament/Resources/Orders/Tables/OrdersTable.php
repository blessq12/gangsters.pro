<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Новый',
                        'preparing' => 'Готовится',
                        'in_transit' => 'В пути',
                        'delivered' => 'Доставлен',
                        default => $state,
                    }),
                TextColumn::make('customer_name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('client_id')
                    ->label('ID клиента')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subtotal')
                    ->label('Подытог')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? number_format(((int) $state) / 100, 2, ',', ' ') . ' ₽'
                            : '—'
                    ),
                TextColumn::make('discount_total')
                    ->label('Скидка')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null && (int) $state !== 0
                            ? number_format(((int) $state) / 100, 2, ',', ' ') . ' ₽'
                            : '0 ₽'
                    ),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? number_format(((int) $state) / 100, 2, ',', ' ') . ' ₽'
                            : '—'
                    ),
                TextColumn::make('delivery_method')
                    ->label('Доставка')
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = DeliveryMethod::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) $state
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_method')
                    ->label('Оплата')
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = PaymentMethod::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) $state
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_status')
                    ->label('Статус оплаты')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = PaymentStatus::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) $state
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
