<?php

namespace App\Filament\Checkout\Resources\Tables;

use App\Filament\Checkout\Support\CheckoutSnapshotReader;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class CheckoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->limit(12)
                    ->tooltip(fn (string $state): string => $state),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => CheckoutSnapshotReader::statusLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'confirmed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('cart_snapshot')
                    ->label('Позиций')
                    ->formatStateUsing(function (mixed $state): string {
                        $lines = is_array($state) && is_array($state['lines'] ?? null)
                            ? $state['lines']
                            : [];

                        return (string) count($lines);
                    }),
                TextColumn::make('cart_total')
                    ->label('Сумма, ₽')
                    ->state(function ($record): int {
                        $cart = is_array($record->cart_snapshot) ? $record->cart_snapshot : [];
                        $lines = is_array($cart['lines'] ?? null) ? $cart['lines'] : [];

                        return CheckoutSnapshotReader::cartTotalRubles($lines);
                    })
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', ' ')),
                TextColumn::make('client_snapshot')
                    ->label('Клиент')
                    ->formatStateUsing(function (mixed $state): string {
                        if (! is_array($state)) {
                            return '—';
                        }

                        if (($state['kind'] ?? '') === 'registered') {
                            $clientId = $state['client_id'] ?? null;

                            return $clientId !== null
                                ? 'Клиент #'.$clientId
                                : 'Авторизованный';
                        }

                        $name = trim((string) ($state['name'] ?? ''));

                        return $name !== '' ? $name : 'Гость';
                    }),
                TextColumn::make('delivery_method')
                    ->label('Доставка')
                    ->state(function ($record): ?string {
                        $delivery = is_array($record->delivery_snapshot) ? $record->delivery_snapshot : [];

                        return isset($delivery['method']) ? (string) $delivery['method'] : null;
                    })
                    ->formatStateUsing(fn (?string $state): string => CheckoutSnapshotReader::deliveryMethodLabel((string) $state)),
                TextColumn::make('payment_method')
                    ->label('Оплата')
                    ->state(function ($record): ?string {
                        $payment = is_array($record->payment_snapshot) ? $record->payment_snapshot : [];

                        return isset($payment['method']) ? (string) $payment['method'] : null;
                    })
                    ->formatStateUsing(fn (?string $state): string => CheckoutSnapshotReader::paymentMethodLabel((string) $state)),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('confirmed_at')
                    ->label('Подтверждено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'confirmed' => 'Подтверждено',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('Оформления не найдены')
            ->emptyStateDescription('Пока нет ни одного объекта оформления.');
    }
}
