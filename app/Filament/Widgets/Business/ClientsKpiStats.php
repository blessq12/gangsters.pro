<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientsKpiStats extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

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
        $kpi = $this->businessSnapshot()->clientsKpi;

        return [
            Stat::make('Новые клиенты', (string) $kpi['new_clients'])
                ->description($this->formatDeltaDescription($kpi['new_clients'], $kpi['previous_new_clients']))
                ->color('success'),
            Stat::make('Всего в базе', (string) $kpi['total_clients'])
                ->color('gray'),
            Stat::make('Согласие на рассылку', (string) $kpi['marketing_consent'])
                ->color('info'),
            Stat::make('Повторные клиенты', (string) $kpi['repeat_clients'])
                ->description('2+ заказа за всё время')
                ->color('primary'),
        ];
    }
}
