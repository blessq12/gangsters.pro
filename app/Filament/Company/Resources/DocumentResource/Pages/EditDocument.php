<?php

namespace App\Filament\Company\Resources\DocumentResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Content\Document\Command\DeleteDocumentUseCase;
use App\Application\Company\Content\Document\Command\SaveDocumentUseCase;
use App\Filament\Company\Pages\ManageCompany;
use App\Filament\Company\Resources\DocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (): void {
                    try {
                        app(DeleteDocumentUseCase::class)->execute((int) $this->getRecord()->getKey());
                        Notification::make()->title('Документ удалён')->success()->send();
                        $this->redirect(ManageCompany::getUrl(['tab' => 'documents']));
                    } catch (ApiException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['id'] = (int) $record->getKey();
        app(SaveDocumentUseCase::class)->execute($data);

        return $record->refresh();
    }
}
