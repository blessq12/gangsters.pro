<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class RecentOrdersTable extends TableWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Последние заказы';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => collect($this->businessSnapshot()->recentOrders))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->description(fn (array $record): string => $record['client_id'] === null ? 'Гость' : 'Клиент'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => OrderStatusLabels::statusLabel($state))
                    ->color(fn (string $state): string => OrderStatusLabels::statusColor($state)),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->formatStateUsing(
                        fn ($state): string => Money::formatKopecksForAdmin((int) $state),
                    ),
                TextColumn::make('id')
                    ->label('')
                    ->formatStateUsing(fn (): string => 'Открыть')
                    ->url(fn (array $record): string => OrderResource::getUrl('view', ['record' => $record['id']])),
            ])
            ->paginated(false);
    }
}
