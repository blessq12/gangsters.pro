<?php

namespace App\Filament\Analytics\Widgets\Hub;

use App\Filament\Analytics\Widgets\Charts\DeliveryMixChartWidget;
use App\Filament\Analytics\Widgets\Charts\OrdersCountChartWidget;
use App\Filament\Analytics\Widgets\Charts\PaymentMixChartWidget;
use App\Filament\Analytics\Widgets\Charts\RevenueTrendChartWidget;
use App\Filament\Analytics\Widgets\Stats\FinanceRevenueStatsWidget;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class HubFinancePanel extends HubAnalyticsPanel
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make(FinanceRevenueStatsWidget::class),
                Livewire::make(RevenueTrendChartWidget::class),
                Livewire::make(OrdersCountChartWidget::class),
                Livewire::make(DeliveryMixChartWidget::class),
                Livewire::make(PaymentMixChartWidget::class),
            ]);
    }
}
