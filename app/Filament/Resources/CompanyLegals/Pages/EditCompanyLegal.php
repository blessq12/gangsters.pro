<?php

namespace App\Filament\Resources\CompanyLegals\Pages;

use App\Filament\Resources\CompanyLegals\CompanyLegalResource;
use Filament\Resources\Pages\EditRecord;

class EditCompanyLegal extends EditRecord
{
    protected static string $resource = CompanyLegalResource::class;

    protected static ?string $title = 'Редактирование юр. данных';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
