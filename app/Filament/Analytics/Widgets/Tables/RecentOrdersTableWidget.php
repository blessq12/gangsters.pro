<?php

namespace App\Filament\Analytics\Widgets\Tables;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Operations\Pages\ManageOperations;
use App\Filament\Operations\Resources\OrderResource;
use App\Filament\Support\BusinessMetricsViewHelper;
use App\Support\Order\OrderStatusLabels;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class RecentOrdersTableWidget extends TableWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Последние заказы';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $rows = collect($this->ordersMetrics()->recentOrders)
                    ->map(fn (array $row): array => $row)
                    ->keyBy('id');

                return new LengthAwarePaginator(
                    $rows,
                    $rows->count(),
                    max(1, $rows->count()),
                    1,
                );
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->formatStateUsing(function (string $state): string {
                        if ($state === '') {
                            return '—';
                        }

                        return \Carbon\CarbonImmutable::parse($state)->format('d.m.Y H:i');
                    }),
                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => OrderStatusLabels::statusLabel($state))
                    ->badge()
                    ->color(fn (array $record): string => OrderStatusLabels::statusColor((string) ($record['status'] ?? ''))),
                TextColumn::make('customer_name')
                    ->label('Клиент'),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->formatStateUsing(fn (int $state): string => BusinessMetricsViewHelper::formatRubles($state)),
            ])
            ->headerActions([
                Action::make('operations')
                    ->label('Все заказы в Операциях')
                    ->url(ManageOperations::getUrl(['tab' => 'orders']))
                    ->link(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => OrderResource::getUrl('edit', ['record' => $record['id']])),
            ])
            ->paginated(false);
    }
}
