<?php

namespace App\Filament\Analytics\Widgets\Stats;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewRevenueStatsWidget extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Ключевые показатели';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $kpi = $this->overviewMetrics()->revenueKpi;

        return [
            Stat::make('Выручка (оплачено)', BusinessMetricsViewHelper::formatRubles($kpi['paid_revenue']))
                ->description(BusinessMetricsViewHelper::deltaDescription($kpi['paid_revenue'], $kpi['previous_paid_revenue'])),
            Stat::make('GMV', BusinessMetricsViewHelper::formatRubles($kpi['gmv']))
                ->description(BusinessMetricsViewHelper::deltaDescription($kpi['gmv'], $kpi['previous_gmv'])),
            Stat::make('Заказы', BusinessMetricsViewHelper::formatInteger($kpi['orders_count']))
                ->description(BusinessMetricsViewHelper::deltaDescription($kpi['orders_count'], $kpi['previous_orders_count'])),
            Stat::make('Средний чек', BusinessMetricsViewHelper::formatRubles($kpi['average_check']))
                ->description(BusinessMetricsViewHelper::deltaDescription($kpi['average_check'], $kpi['previous_average_check'])),
        ];
    }
}
