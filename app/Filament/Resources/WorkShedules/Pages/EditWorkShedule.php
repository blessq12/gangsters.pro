<?php

namespace App\Filament\Resources\WorkShedules\Pages;

use App\Filament\Resources\WorkShedules\WorkSheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkShedule extends EditRecord
{
    protected static string $resource = WorkSheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
