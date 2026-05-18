<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Domain\Order\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы';

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'заказы';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->limit(12)
                    ->tooltip(fn ($record): string => $record->id),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => OrderStatusLabels::statusLabel($state))
                    ->color(fn (string $state): string => OrderStatusLabels::statusColor($state)),
                TextColumn::make('payment_status')
                    ->label('Оплата')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => $state !== null && ($enum = PaymentStatus::tryFrom($state)) !== null
                            ? $enum->label()
                            : (string) ($state ?? '—'),
                    )
                    ->color(fn (?string $state): string => OrderStatusLabels::paymentStatusColor($state)),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—',
                    ),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderStatusLabels::statusOptions()),
            ])
            ->headerActions([
                // Заказы создаются на сайте или через интеграции.
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Открыть')
                    ->url(
                        fn ($record): string => OrderResource::getUrl('view', ['record' => $record]),
                    )
                    ->openUrlInNewTab(),
            ], position: RecordActionsPosition::BeforeCells);
    }
}
