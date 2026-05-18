<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Concerns\ClientWorkflowHeaderActions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    use ClientWorkflowHeaderActions;

    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Редактирование клиента';

    protected function getHeaderActions(): array
    {
        return $this->getClientWorkflowHeaderActions();
    }
}
