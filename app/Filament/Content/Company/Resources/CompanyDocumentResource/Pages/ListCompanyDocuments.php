<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages;

use App\Domain\Content\Repository\CompanyRepository;
use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use App\Infrastructure\Content\Model\CMP_CompanyDocument;
use Filament\Resources\Pages\ListRecords;

class ListCompanyDocuments extends ListRecords
{
    protected static string $resource = CompanyDocumentResource::class;

    protected static ?string $title = 'Документы';

    protected static ?string $navigationLabel = 'Документы';

    public function mount(): void
    {
        $this->ensureDocuments();

        parent::mount();
    }

    private function ensureDocuments(): void
    {
        foreach (CompanyDocumentResource::documentDefinitions() as $key => $title) {
            CMP_CompanyDocument::query()->firstOrCreate(
                [
                    'company_id' => CompanyRepository::SINGLETON_ID,
                    'key' => $key,
                ],
                [
                    'title' => $title,
                    'content' => null,
                ],
            );
        }
    }
}
