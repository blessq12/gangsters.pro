<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages;

use App\Domain\Content\Repository\CompanyRepository;
use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use App\Infrastructure\Content\Model\CMP_CompanyDocument;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCompanyDocument extends CreateRecord
{
    protected static string $resource = CompanyDocumentResource::class;

    protected static ?string $title = 'Новый документ';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = CompanyRepository::SINGLETON_ID;
        $data['slug'] = $this->uniqueSlugFromName((string) ($data['name'] ?? ''));

        $content = $data['content'] ?? null;
        if (! is_string($content) || trim(strip_tags($content)) === '') {
            $data['content'] = null;
        }

        return $data;
    }

    private function uniqueSlugFromName(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'document';
        }

        $slug = $base;
        $suffix = 2;
        while (CMP_CompanyDocument::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Документ создан');
    }
}
