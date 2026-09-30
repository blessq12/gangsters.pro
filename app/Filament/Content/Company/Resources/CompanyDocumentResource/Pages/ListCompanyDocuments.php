<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages;

use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use Filament\Resources\Pages\ListRecords;

class ListCompanyDocuments extends ListRecords
{
    protected static string $resource = CompanyDocumentResource::class;

    protected static ?string $title = 'Документы';

    protected static ?string $navigationLabel = 'Документы';
}
