<?php

namespace App\Filament\Analytics\Widgets\Stats;

use App\Filament\Analytics\Widgets\Concerns\InteractsWithBusinessMetrics;
use App\Filament\Support\BusinessMetricsViewHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShoppingFunnelStatsWidget extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Витрина и корзины';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $funnel = $this->storefrontMetrics()->shoppingFunnel;

        return [
            Stat::make('Активные сессии', BusinessMetricsViewHelper::formatInteger($funnel['active_sessions'])),
            Stat::make('Строк в корзинах', BusinessMetricsViewHelper::formatInteger($funnel['cart_lines'])),
            Stat::make('Checkout drafts', BusinessMetricsViewHelper::formatInteger($funnel['checkout_drafts'])),
        ];
    }
}
