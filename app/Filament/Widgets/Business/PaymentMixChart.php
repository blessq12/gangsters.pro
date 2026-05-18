<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\ChartWidget;

class PaymentMixChart extends ChartWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 9;

    protected ?string $heading = 'Способ оплаты';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '220px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $mix = $this->businessSnapshot()->paymentMix;

        return [
            'datasets' => [
                [
                    'data' => $mix['values'],
                ],
            ],
            'labels' => $mix['labels'],
        ];
    }
}
