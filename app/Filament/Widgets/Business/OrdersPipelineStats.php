<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use App\Support\Order\OrderStatusLabels;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersPipelineStats extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    protected function getStats(): array
    {
        $pipeline = $this->businessSnapshot()->ordersPipeline;
        $stats = [];

        foreach (OrderStatusLabels::statusTabKeys() as $key) {
            if ($key === 'all') {
                continue;
            }

            $stats[] = Stat::make(
                OrderStatusLabels::statusTabLabel($key),
                (string) ($pipeline[$key] ?? 0),
            )
                ->color(OrderStatusLabels::statusColor($key))
                ->url(ListOrders::getUrl(['tab' => $key]));
        }

        return $stats;
    }
}
