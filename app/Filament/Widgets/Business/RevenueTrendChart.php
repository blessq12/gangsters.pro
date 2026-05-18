<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use App\Support\Money;
use Filament\Widgets\ChartWidget;

class RevenueTrendChart extends ChartWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 6;

    protected ?string $heading = 'Выручка (оплачено)';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '220px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $trend = $this->businessSnapshot()->revenueTrend;
        $values = array_map(
            fn (int $kopecks): float => Money::kopecksToApiRubles($kopecks),
            $trend['values'],
        );

        return [
            'datasets' => [
                [
                    'label' => 'Выручка, ₽',
                    'data' => $values,
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }
}
