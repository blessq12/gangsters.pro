<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withCount('items'),
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->limit(12)
                    ->tooltip(fn ($record): string => $record->id),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => OrderStatusLabels::statusLabel($state))
                    ->color(fn (string $state): string => OrderStatusLabels::statusColor($state)),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->searchable()
                    ->description(fn ($record): ?string => $record->customer_phone),
                TextColumn::make('client_id')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => $state === null ? 'Гость' : 'Клиент',
                    )
                    ->color(
                        fn ($state): string => $state === null ? 'warning' : 'gray',
                    ),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—',
                    ),
                TextColumn::make('payment_status')
                    ->label('Оплата')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = PaymentStatus::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) $state,
                    )
                    ->color(
                        fn (?string $state): string => OrderStatusLabels::paymentStatusColor($state),
                    ),
                TextColumn::make('delivery_method')
                    ->label('Доставка')
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = DeliveryMethod::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) ($state ?? '—'),
                    )
                    ->toggleable(),
                TextColumn::make('items_count')
                    ->label('Поз.')
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Подытог')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—',
                    ),
                TextColumn::make('discount_total')
                    ->label('Скидка')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(
                        fn ($state): string => $state !== null && (int) $state !== 0
                            ? Money::formatKopecksForAdmin((int) $state)
                            : Money::formatKopecksForAdmin(0),
                    ),
                TextColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = PaymentMethod::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) ($state ?? '—'),
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Статус оплаты')
                    ->options(PaymentStatus::options()),
                SelectFilter::make('delivery_method')
                    ->label('Доставка')
                    ->options(DeliveryMethod::options()),
                SelectFilter::make('payment_method')
                    ->label('Оплата')
                    ->options(PaymentMethod::allOptions()),
                Filter::make('guest')
                    ->label('Только гости')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('client_id')),
                Filter::make('unpaid')
                    ->label('Не оплачен')
                    ->toggle()
                    ->query(
                        fn (Builder $query): Builder => $query->where(
                            'payment_status',
                            PaymentStatus::Unpaid->value,
                        ),
                    ),
                Filter::make('created_at')
                    ->label('Дата создания')
                    ->schema([
                        DatePicker::make('created_from')->label('С'),
                        DatePicker::make('created_until')->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markPreparing')
                        ->label('В готовку')
                        ->icon('heroicon-o-fire')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['status' => 'preparing']),
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markInTransit')
                        ->label('В пути')
                        ->icon('heroicon-o-truck')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['status' => 'in_transit']),
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markDelivered')
                        ->label('Доставлен')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['status' => 'delivered']),
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markPaid')
                        ->label('Оплачен')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $markPaid = app(\App\Application\Order\Contracts\MarkOrderPaidContract::class);
                            foreach ($records as $record) {
                                if ($record->payment_status !== PaymentStatus::Paid->value) {
                                    $markPaid->execute($record->id);
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
