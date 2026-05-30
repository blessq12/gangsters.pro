<?php

namespace App\Filament\Analytics\Widgets\Charts;

class RevenueTrendChartWidget extends AnalyticsChartWidget
{
    protected ?string $heading = 'Выручка по периоду';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $trend = $this->financeMetrics()->revenueTrend;

        return [
            'datasets' => [
                [
                    'label' => 'Выручка, ₽',
                    'data' => $this->kopecksToRubles($trend['values']),
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }
}
