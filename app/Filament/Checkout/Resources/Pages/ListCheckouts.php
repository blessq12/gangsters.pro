<?php

namespace App\Filament\Checkout\Resources\Pages;

use App\Filament\Checkout\Resources\CheckoutResource;
use Filament\Resources\Pages\ListRecords;

class ListCheckouts extends ListRecords
{
    protected static string $resource = CheckoutResource::class;

    protected static ?string $title = 'Оформления';

    protected static ?string $navigationLabel = 'Оформления';

    /**
     * @return list<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
