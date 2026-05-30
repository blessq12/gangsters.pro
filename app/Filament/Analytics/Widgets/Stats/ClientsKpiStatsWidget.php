<?php

namespace App\Filament\Analytics\Widgets\Stats;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientsKpiStatsWidget extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Клиенты';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $kpi = $this->clientsMetrics()->clientsKpi;

        return [
            Stat::make('Новые клиенты', BusinessMetricsViewHelper::formatInteger($kpi['new_clients']))
                ->description(BusinessMetricsViewHelper::deltaDescription($kpi['new_clients'], $kpi['previous_new_clients'])),
            Stat::make('Всего клиентов', BusinessMetricsViewHelper::formatInteger($kpi['total_clients'])),
            Stat::make('Согласие на маркетинг', BusinessMetricsViewHelper::formatInteger($kpi['marketing_consent'])),
            Stat::make('Повторные клиенты', BusinessMetricsViewHelper::formatInteger($kpi['repeat_clients'])),
        ];
    }
}
