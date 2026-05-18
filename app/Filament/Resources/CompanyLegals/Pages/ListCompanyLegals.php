<?php

namespace App\Filament\Resources\CompanyLegals\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\CompanyLegals\CompanyLegalResource;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Filament\Resources\Pages\ListRecords;

class ListCompanyLegals extends ListRecords
{
    protected static string $resource = CompanyLegalResource::class;

    protected static ?string $title = 'Юр. данные компании';

    public function mount(): void
    {
        $company = SYS_Company::query()->firstOrCreate(
            ['id' => 1],
            ['name' => 'Компания']
        );

        $this->redirect(
            CompanyResource::getUrl('edit', ['record' => $company]),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
