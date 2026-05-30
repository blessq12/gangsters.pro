<?php

namespace App\Filament\Company\Resources\DocumentResource\Pages;

use App\Application\Company\Content\Document\Command\SaveDocumentUseCase;
use App\Filament\Company\Resources\DocumentResource;
use App\Infrastructure\SystemContent\Model\SYS_Document;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $saved = app(SaveDocumentUseCase::class)->execute($data);

        return SYS_Document::query()->findOrFail($saved['id']);
    }
}
