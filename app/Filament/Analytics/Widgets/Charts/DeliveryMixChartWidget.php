<?php

namespace App\Filament\Analytics\Widgets\Charts;

class DeliveryMixChartWidget extends AnalyticsChartWidget
{
    protected ?string $heading = 'Способы доставки';

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $mix = $this->financeMetrics()->deliveryMix;

        return [
            'datasets' => [
                [
                    'label' => 'Заказы',
                    'data' => $mix['values'],
                ],
            ],
            'labels' => $mix['labels'],
        ];
    }
}
