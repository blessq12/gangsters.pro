<?php

namespace App\Filament\Analytics\Widgets\Hub;

use App\Filament\Analytics\Widgets\Stats\ShoppingFunnelStatsWidget;
use App\Filament\Analytics\Widgets\Tables\TopProductsTableWidget;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class HubStorefrontPanel extends HubAnalyticsPanel
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make(ShoppingFunnelStatsWidget::class),
                Livewire::make(TopProductsTableWidget::class),
            ]);
    }
}
