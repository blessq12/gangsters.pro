<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Concerns\ClientWorkflowHeaderActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    use ClientWorkflowHeaderActions;

    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Просмотр клиента';

    protected function getHeaderActions(): array
    {
        if ($this->getRecord()->trashed()) {
            return [
                ...$this->getClientTrashHeaderActions(),
            ];
        }

        return [
            EditAction::make(),
            ...$this->getClientStatusHeaderActions(),
        ];
    }
}
