<?php

namespace App\Filament\Resources\WorkShedules\Pages;

use App\Filament\Resources\WorkShedules\WorkSheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkShedules extends ListRecords
{
    protected static string $resource = WorkSheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
