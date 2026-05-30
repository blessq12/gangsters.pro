<?php

namespace App\Filament\Analytics\Widgets\Charts;

class OrdersCountChartWidget extends AnalyticsChartWidget
{
    protected ?string $heading = 'Заказы по периоду';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $trend = $this->financeMetrics()->ordersCountTrend;

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
