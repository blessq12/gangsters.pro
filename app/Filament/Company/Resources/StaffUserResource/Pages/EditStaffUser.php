<?php

namespace App\Filament\Company\Resources\StaffUserResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Staff\Command\DeleteAdminUserUseCase;
use App\Application\Company\Staff\Command\UpdateAdminUserUseCase;
use App\Filament\Company\Pages\ManageCompany;
use App\Filament\Company\Resources\StaffUserResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStaffUser extends EditRecord
{
    protected static string $resource = StaffUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (): void {
                    try {
                        app(DeleteAdminUserUseCase::class)->execute(
                            (int) $this->getRecord()->getKey(),
                            (int) auth()->id(),
                        );
                        Notification::make()->title('Сотрудник удалён')->success()->send();
                        $this->redirect(ManageCompany::getUrl(['tab' => 'staff']));
                    } catch (ApiException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(UpdateAdminUserUseCase::class)->execute((int) $record->getKey(), $data);

        return $record->refresh();
    }
}
