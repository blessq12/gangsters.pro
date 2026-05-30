<?php

namespace App\Filament\Analytics\Widgets\Hub;

use App\Filament\Analytics\Widgets\Stats\ClientsKpiStatsWidget;
use App\Filament\Analytics\Widgets\Tables\TopClientsTableWidget;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class HubClientsPanel extends HubAnalyticsPanel
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make(ClientsKpiStatsWidget::class),
                Livewire::make(TopClientsTableWidget::class),
            ]);
    }
}
