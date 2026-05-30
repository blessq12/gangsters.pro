<?php

namespace App\Filament\Analytics\Widgets\Hub;

use App\Filament\Analytics\Widgets\Stats\OverviewPipelineStatsWidget;
use App\Filament\Analytics\Widgets\Stats\OverviewRevenueStatsWidget;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class HubOverviewPanel extends HubAnalyticsPanel
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make(OverviewRevenueStatsWidget::class),
                Livewire::make(OverviewPipelineStatsWidget::class),
            ]);
    }
}
