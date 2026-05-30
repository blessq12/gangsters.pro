<?php

namespace App\Filament\Analytics\Widgets\Tables;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class TopProductsTableWidget extends TableWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Топ товаров';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $rows = collect($this->storefrontMetrics()->topProducts)
                    ->values()
                    ->map(fn (array $row, int $index): array => [
                        'id' => $row['product_original_id'] ?? ('row-'.$index),
                        'product_name' => $row['product_name'],
                        'quantity' => $row['quantity'],
                        'revenue' => $row['revenue'],
                    ])
                    ->keyBy('id');

                return new LengthAwarePaginator(
                    $rows,
                    $rows->count(),
                    max(1, $rows->count()),
                    1,
                );
            })
            ->columns([
                TextColumn::make('product_name')
                    ->label('Товар'),
                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->formatStateUsing(fn (int $state): string => BusinessMetricsViewHelper::formatInteger($state)),
                TextColumn::make('revenue')
                    ->label('Выручка')
                    ->formatStateUsing(fn (int $state): string => BusinessMetricsViewHelper::formatRubles($state)),
            ])
            ->paginated(false);
    }
}
