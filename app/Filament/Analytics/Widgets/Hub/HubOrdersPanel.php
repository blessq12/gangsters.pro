<?php

namespace App\Filament\Analytics\Widgets\Hub;

use App\Filament\Analytics\Widgets\Stats\ChannelStatsWidget;
use App\Filament\Analytics\Widgets\Stats\OrdersPipelineStatsWidget;
use App\Filament\Analytics\Widgets\Tables\RecentOrdersTableWidget;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class HubOrdersPanel extends HubAnalyticsPanel
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make(OrdersPipelineStatsWidget::class),
                Livewire::make(ChannelStatsWidget::class),
                Livewire::make(RecentOrdersTableWidget::class),
            ]);
    }
}
