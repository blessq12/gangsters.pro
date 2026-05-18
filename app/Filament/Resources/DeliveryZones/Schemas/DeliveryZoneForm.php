<?php

namespace App\Filament\Resources\DeliveryZones\Schemas;

use App\Filament\Resources\Companies\Schemas\CompanyDeliveryZoneTabSchema;
use Filament\Schemas\Schema;

final class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(CompanyDeliveryZoneTabSchema::sections());
    }
}
