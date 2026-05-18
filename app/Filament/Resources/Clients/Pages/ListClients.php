<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Pages\ManageUsers;
use App\Filament\Resources\Clients\ClientResource;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Клиенты';

    public function mount(): void
    {
        $this->redirect(
            ManageUsers::getUrl(['tab' => 'clients']),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
