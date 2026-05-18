<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChannelStats extends StatsOverviewWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    protected function getStats(): array
    {
        $channels = $this->businessSnapshot()->channelStats;

        return [
            Stat::make('Сайт', (string) $channels['site_orders'])
                ->color('primary'),
            Stat::make('Яндекс Еда', (string) $channels['yandex_orders'])
                ->color('warning'),
            Stat::make('Авторизованные', (string) $channels['registered_orders'])
                ->color('success'),
            Stat::make('Гости', (string) $channels['guest_orders'])
                ->color('gray'),
        ];
    }
}
