<?php

namespace App\Filament\Company\Resources\StaffUserResource\Pages;

use App\Application\Company\Staff\Command\CreateAdminUserUseCase;
use App\Filament\Company\Resources\StaffUserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStaffUser extends CreateRecord
{
    protected static string $resource = StaffUserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $saved = app(CreateAdminUserUseCase::class)->execute($data);

        return User::query()->findOrFail($saved['id']);
    }
}
