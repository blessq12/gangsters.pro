<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    public function mount(int|string $record): void
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
}
