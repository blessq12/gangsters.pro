<?php

namespace App\Filament\Analytics\Widgets\Charts;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\ChartWidget;

abstract class AnalyticsChartWidget extends ChartWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    /**
     * @param  list<int>  $valuesKopecks
     * @return list<float>
     */
    protected function kopecksToRubles(array $valuesKopecks): array
    {
        return array_map(
            static fn (int $value): float => round($value / 100, 2),
            $valuesKopecks,
        );
    }
}
