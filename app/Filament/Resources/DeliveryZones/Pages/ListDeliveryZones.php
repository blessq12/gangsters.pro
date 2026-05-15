<?php

namespace App\Filament\Resources\DeliveryZones\Pages;

use App\Filament\Resources\DeliveryZones\DeliveryZoneResource;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryZones extends ListRecords
{
    protected static string $resource = DeliveryZoneResource::class;

    protected static ?string $title = 'Зона доставки';

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
