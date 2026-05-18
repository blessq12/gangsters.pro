<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShoppingFunnelStats extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    protected function getStats(): array
    {
        $funnel = $this->businessSnapshot()->shoppingFunnel;

        return [
            Stat::make('Активные корзины', (string) $funnel['active_sessions'])
                ->color('info'),
            Stat::make('Строк в корзинах', (string) $funnel['cart_lines'])
                ->color('primary'),
            Stat::make('Черновики checkout', (string) $funnel['checkout_drafts'])
                ->color('warning'),
        ];
    }
}
