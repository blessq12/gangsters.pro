<?php

namespace App\Filament\Analytics\Widgets\Stats;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChannelStatsWidget extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Каналы заказов';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = $this->ordersMetrics()->channelStats;

        return [
            Stat::make('Заказы (рег.)', BusinessMetricsViewHelper::formatInteger($stats['registered_orders'])),
            Stat::make('Гостевые', BusinessMetricsViewHelper::formatInteger($stats['guest_orders'])),
            Stat::make('Yandex Food', BusinessMetricsViewHelper::formatInteger($stats['yandex_orders'])),
            Stat::make('Сайт', BusinessMetricsViewHelper::formatInteger($stats['site_orders'])),
        ];
    }
}
