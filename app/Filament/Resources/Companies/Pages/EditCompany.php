<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\DeliveryZones\DeliveryZoneResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected static ?string $title = 'Редактирование компании';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deliveryZone')
                ->label('Зона доставки на карте')
                ->url(fn (): string => DeliveryZoneResource::getUrl('edit', ['record' => $this->getRecord()]))
                ->icon('heroicon-o-map'),
        ];
    }
}
