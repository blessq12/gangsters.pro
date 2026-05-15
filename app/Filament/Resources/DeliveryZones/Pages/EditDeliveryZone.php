<?php

namespace App\Filament\Resources\DeliveryZones\Pages;

use App\Filament\Resources\DeliveryZones\DeliveryZoneResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditDeliveryZone extends EditRecord
{
    protected static string $resource = DeliveryZoneResource::class;

    protected static ?string $title = 'Зона доставки';

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Сохраняем только поля зоны — без дублирования адреса и city_coverage.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return Arr::only($data, [
            'delivery_zone_geojson',
            'kitchen_latitude',
            'kitchen_longitude',
        ]);
    }
}
