<?php

namespace App\Filament\Order\Resources\Tables;

use App\Filament\Order\Support\OrderSnapshotReader;
use App\Filament\Support\ClientSnapshotLabel;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Источник')
                    ->formatStateUsing(fn (?string $state): string => OrderSnapshotReader::sourceLabel((string) $state))
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'aggregator' ? 'warning' : 'gray')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => OrderSnapshotReader::statusLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'preparing' => 'warning',
                        'in_transit' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total_rubles')
                    ->label('Сумма, ₽')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', ' '))
                    ->sortable(),
                TextColumn::make('client_label')
                    ->label('Клиент')
                    ->state(function ($record): string {
                        return ClientSnapshotLabel::forList(
                            $record->client_snapshot,
                            $record->client_id !== null ? (int) $record->client_id : null,
                        );
                    }),
                TextColumn::make('delivery_method')
                    ->label('Доставка')
                    ->state(function ($record): ?string {
                        $delivery = is_array($record->delivery_snapshot) ? $record->delivery_snapshot : [];

                        return isset($delivery['method']) ? (string) $delivery['method'] : null;
                    })
                    ->formatStateUsing(fn (?string $state): string => OrderSnapshotReader::deliveryMethodLabel((string) $state)),
                TextColumn::make('payment_method')
                    ->label('Оплата')
                    ->state(function ($record): ?string {
                        $payment = is_array($record->payment_snapshot) ? $record->payment_snapshot : [];

                        return isset($payment['method']) ? (string) $payment['method'] : null;
                    })
                    ->formatStateUsing(fn (?string $state): string => OrderSnapshotReader::paymentMethodLabel((string) $state)),
                TextColumn::make('checkout_id')
                    ->label('Checkout')
                    ->searchable()
                    ->copyable()
                    ->limit(12)
                    ->tooltip(fn (?string $state): string => (string) $state)
                    ->placeholder('—'),
                TextColumn::make('partner_code')
                    ->label('Партнёр')
                    ->formatStateUsing(fn (?string $state): string => OrderSnapshotReader::partnerLabel($state))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('external_order_id')
                    ->label('Внешний ID')
                    ->searchable()
                    ->copyable()
                    ->limit(16)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новый',
                        'preparing' => 'Готовится',
                        'in_transit' => 'В доставке',
                        'delivered' => 'Доставлен',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('Заказы не найдены')
            ->emptyStateDescription('Пока нет ни одного подтверждённого заказа.');
    }
}
