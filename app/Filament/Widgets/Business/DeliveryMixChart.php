<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\ChartWidget;

class DeliveryMixChart extends ChartWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 8;

    protected ?string $heading = 'Способ получения';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '220px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $mix = $this->businessSnapshot()->deliveryMix;

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
