<?php

namespace App\Filament\Crm\Resources\ClientResource\Pages;

use App\Filament\Crm\Resources\ClientResource;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Клиенты';

    protected static ?string $navigationLabel = 'Клиенты';

    /**
     * @return list<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
