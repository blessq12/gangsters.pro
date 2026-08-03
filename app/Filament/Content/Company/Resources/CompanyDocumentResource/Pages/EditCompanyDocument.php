<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages;

use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCompanyDocument extends EditRecord
{
    protected static string $resource = CompanyDocumentResource::class;

    protected static ?string $title = 'Редактирование документа';

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $content = $data['content'] ?? null;
        if (! is_string($content) || trim(strip_tags($content)) === '') {
            $data['content'] = null;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Документ сохранён');
    }
}
