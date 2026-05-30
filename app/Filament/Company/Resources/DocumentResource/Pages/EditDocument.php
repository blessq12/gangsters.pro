<?php

namespace App\Filament\Company\Resources\DocumentResource\Pages;

use App\Application\Company\Content\Document\Command\SaveDocumentUseCase;
use App\Filament\Company\Resources\DocumentResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['id'] = (int) $record->getKey();
        app(SaveDocumentUseCase::class)->execute($data);

        return $record->refresh();
    }
}
