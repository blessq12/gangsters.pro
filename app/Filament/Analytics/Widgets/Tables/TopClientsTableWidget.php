<?php

namespace App\Filament\Analytics\Widgets\Tables;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class TopClientsTableWidget extends TableWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Топ клиентов';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $rows = collect($this->clientsMetrics()->topClients)
                    ->map(fn (array $row): array => [
                        'client_id' => $row['client_id'],
                        'client_name' => $row['client_name'],
                        'orders_count' => $row['orders_count'],
                        'revenue' => $row['revenue'],
                    ])
                    ->keyBy('client_id');

                return new LengthAwarePaginator(
                    $rows,
                    $rows->count(),
                    max(1, $rows->count()),
                    1,
                );
            })
            ->columns([
                TextColumn::make('client_name')
                    ->label('Клиент'),
                TextColumn::make('orders_count')
                    ->label('Заказов')
                    ->formatStateUsing(fn (int $state): string => BusinessMetricsViewHelper::formatInteger($state)),
                TextColumn::make('revenue')
                    ->label('Выручка')
                    ->formatStateUsing(fn (int $state): string => BusinessMetricsViewHelper::formatRubles($state)),
            ])
            ->paginated(false);
    }
}
