<?php

namespace App\Filament\Resources\CompanyLegals\Pages;

use App\Filament\Resources\CompanyLegals\CompanyLegalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyLegal extends EditRecord
{
    protected static string $resource = CompanyLegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
