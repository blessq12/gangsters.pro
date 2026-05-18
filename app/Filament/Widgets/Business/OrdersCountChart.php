<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\ChartWidget;

class OrdersCountChart extends ChartWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 7;

    protected ?string $heading = 'Количество заказов';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '220px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $trend = $this->businessSnapshot()->ordersCountTrend;

        return [
            'datasets' => [
                [
                    'label' => 'Заказы',
                    'data' => $trend['values'],
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }
}
