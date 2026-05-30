<?php

namespace App\Filament\Analytics\Widgets\Stats;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use App\Support\Order\OrderStatusLabels;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersPipelineStatsWidget extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Воронка заказов';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $pipeline = $this->ordersMetrics()->ordersPipeline;
        $stats = [];

        foreach (OrderStatusLabels::statusTabKeys() as $key) {
            if ($key === 'all') {
                continue;
            }

            $stats[] = Stat::make(
                OrderStatusLabels::statusLabel($key),
                BusinessMetricsViewHelper::formatInteger((int) ($pipeline[$key] ?? 0)),
            );
        }

        return $stats;
    }
}
