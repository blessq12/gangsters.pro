<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Просмотр клиента';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

