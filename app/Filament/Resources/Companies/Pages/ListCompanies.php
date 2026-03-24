<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected static ?string $title = 'Компании';

    public function mount(): void
    {
        $company = SYS_Company::query()->firstOrCreate(
            ['id' => 1],
            ['name' => 'Компания']
        );

        $this->redirect(
            static::getResource()::getUrl('edit', ['record' => $company]),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
