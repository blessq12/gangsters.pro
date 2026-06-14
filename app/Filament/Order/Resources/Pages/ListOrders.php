<?php

namespace App\Filament\Order\Resources\Pages;

use App\Filament\Order\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Заказы';

    protected static ?string $navigationLabel = 'Заказы';

    /**
     * @return list<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
