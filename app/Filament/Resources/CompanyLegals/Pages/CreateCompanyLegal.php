<?php

namespace App\Filament\Resources\CompanyLegals\Pages;

use App\Filament\Resources\CompanyLegals\CompanyLegalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyLegal extends CreateRecord
{
    protected static string $resource = CompanyLegalResource::class;

    protected static ?string $title = 'Новая юр. запись';
}
