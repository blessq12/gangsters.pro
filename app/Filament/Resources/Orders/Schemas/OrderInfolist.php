<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Заказ')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')->label('Номер заказа')->copyable(),
                        TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime('d.m.Y H:i'),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => OrderStatusLabels::statusLabel($state))
                            ->color(fn (string $state): string => OrderStatusLabels::statusColor($state)),
                        TextEntry::make('payment_status')
                            ->label('Статус оплаты')
                            ->badge()
                            ->formatStateUsing(
                                fn (?string $state): string => $state !== null && ($enum = PaymentStatus::tryFrom($state)) !== null
                                    ? $enum->label()
                                    : (string) $state,
                            )
                            ->color(fn (?string $state): string => OrderStatusLabels::paymentStatusColor($state)),
                        TextEntry::make('subtotal')
                            ->label('Подытог')
                            ->formatStateUsing(
                                fn ($state): string => Money::formatKopecksForAdmin((int) ($state ?? 0)),
                            ),
                        TextEntry::make('discount_total')
                            ->label('Скидка')
                            ->formatStateUsing(
                                fn ($state): string => Money::formatKopecksForAdmin((int) ($state ?? 0)),
                            ),
                        TextEntry::make('total')
                            ->label('Итого')
                            ->formatStateUsing(
                                fn ($state): string => Money::formatKopecksForAdmin((int) ($state ?? 0)),
                            ),
                    ]),
                Section::make('Клиент')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('client_id')
                            ->label('Тип')
                            ->formatStateUsing(
                                fn ($state): string => $state === null ? 'Гость' : 'Клиент #'.$state,
                            ),
                        TextEntry::make('customer_name')->label('Имя'),
                        TextEntry::make('customer_phone')->label('Телефон'),
                        TextEntry::make('customer_email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('customer_address')
                            ->label('Адрес клиента')
                            ->columnSpanFull()
                            ->formatStateUsing(
                                fn ($state): ?string => OrderStatusLabels::formatAddress(
                                    is_array($state) ? $state : null,
                                ),
                            )
                            ->placeholder('—'),
                    ]),
                Section::make('Доставка и оплата')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('delivery_method')
                            ->label('Способ доставки')
                            ->formatStateUsing(
                                fn (?string $state): string => $state !== null && ($enum = DeliveryMethod::tryFrom($state)) !== null
                                    ? $enum->label()
                                    : (string) ($state ?? '—'),
                            ),
                        TextEntry::make('payment_method')
                            ->label('Способ оплаты')
                            ->formatStateUsing(
                                fn (?string $state): string => $state !== null && ($enum = PaymentMethod::tryFrom($state)) !== null
                                    ? $enum->label()
                                    : (string) ($state ?? '—'),
                            ),
                        TextEntry::make('delivery_address')
                            ->label('Адрес доставки')
                            ->columnSpanFull()
                            ->formatStateUsing(
                                fn ($state): ?string => OrderStatusLabels::formatAddress(
                                    is_array($state) ? $state : null,
                                ),
                            )
                            ->placeholder('—'),
                        TextEntry::make('delivery_comment')
                            ->label('Комментарий')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),
                Section::make('Состав заказа')
                    ->schema([
                        TextEntry::make('items_summary')
                            ->label('Позиции')
                            ->columnSpanFull()
                            ->state(function (ORD_Order $record): string {
                                $record->loadMissing('items');
                                if ($record->items->isEmpty()) {
                                    return '—';
                                }

                                $lines = [];
                                foreach ($record->items as $item) {
                                    $lines[] = sprintf(
                                        '%s × %d — %s',
                                        $item->product_name,
                                        (int) $item->quantity,
                                        Money::formatKopecksForAdmin((int) $item->row_total),
                                    );
                                }

                                return implode("\n", $lines);
                            }),
                    ]),
            ]);
    }
}
