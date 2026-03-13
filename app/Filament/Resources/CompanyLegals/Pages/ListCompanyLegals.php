<?php

namespace App\Filament\Resources\CompanyLegals\Pages;

use App\Filament\Resources\CompanyLegals\CompanyLegalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyLegals extends ListRecords
{
    protected static string $resource = CompanyLegalResource::class;

    protected static ?string $title = 'Юр. данные компании';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
