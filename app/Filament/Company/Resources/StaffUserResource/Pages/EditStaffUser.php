<?php

namespace App\Filament\Company\Resources\StaffUserResource\Pages;

use App\Application\Company\Staff\Command\UpdateAdminUserUseCase;
use App\Filament\Company\Resources\StaffUserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStaffUser extends EditRecord
{
    protected static string $resource = StaffUserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(UpdateAdminUserUseCase::class)->execute((int) $record->getKey(), $data);

        return $record->refresh();
    }
}
