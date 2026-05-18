<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewKpiStats extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    /**
     * @return int|array<string, int|null>|null
     */
    protected function getColumns(): int|array|null
    {
        return 4;
    }

    protected function getStats(): array
    {
        $snapshot = $this->businessSnapshot();
        $revenue = $snapshot->revenueKpi;
        $clients = $snapshot->clientsKpi;

        return [
            Stat::make('Выручка (оплачено)', Money::formatKopecksForAdmin($revenue['paid_revenue']))
                ->description($this->formatDeltaDescription($revenue['paid_revenue'], $revenue['previous_paid_revenue']))
                ->color('success'),
            Stat::make('GMV', Money::formatKopecksForAdmin($revenue['gmv']))
                ->description($this->formatDeltaDescription($revenue['gmv'], $revenue['previous_gmv']))
                ->color('gray'),
            Stat::make('Средний чек', Money::formatKopecksForAdmin($revenue['average_check']))
                ->description($this->formatDeltaDescription($revenue['average_check'], $revenue['previous_average_check']))
                ->color('primary'),
            Stat::make('Заказы', (string) $revenue['orders_count'])
                ->description($this->formatDeltaDescription($revenue['orders_count'], $revenue['previous_orders_count']))
                ->color('info'),
            Stat::make('Новые клиенты', (string) $clients['new_clients'])
                ->description($this->formatDeltaDescription($clients['new_clients'], $clients['previous_new_clients']))
                ->color('success'),
        ];
    }
}
