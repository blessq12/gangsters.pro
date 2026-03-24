<?php

namespace App\Filament\Resources\CompanyLegals\Pages;

use App\Filament\Resources\CompanyLegals\CompanyLegalResource;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;
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

        $legal = SYS_CompanyLegal::query()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'legal_form' => 'Не указано',
                'legal_email' => 'noreply@example.com',
                'owner' => 'Не указано',
                'inn' => '0000000000',
                'ogrn' => '0000000000000',
                'okpo' => '00000000',
                'kpp' => '000000000',
                'registration_address' => 'Не указано',
            ]
        );

        $this->redirect(
            static::getResource()::getUrl('edit', ['record' => $legal]),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
