<?php

namespace App\Filament\Analytics\Widgets\Charts;

class PaymentMixChartWidget extends AnalyticsChartWidget
{
    protected ?string $heading = 'Способы оплаты';

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $mix = $this->financeMetrics()->paymentMix;

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
