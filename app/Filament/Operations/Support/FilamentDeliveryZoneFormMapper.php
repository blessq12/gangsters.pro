<?php

namespace App\Filament\Operations\Support;

use App\Application\Operations\Delivery\DTO\UpdateDeliveryZoneDto;

final class FilamentDeliveryZoneFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        return [
            'company_name' => $detail['company_name'] ?? '',
            'delivery_zone_geojson' => $detail['delivery_zone_geojson'] ?? null,
            'kitchen_address' => '',
            'kitchen_latitude' => $detail['kitchen_latitude'] ?? null,
            'kitchen_longitude' => $detail['kitchen_longitude'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): UpdateDeliveryZoneDto
    {
        return new UpdateDeliveryZoneDto(
            deliveryZoneGeojson: $data['delivery_zone_geojson'] ?? null,
            kitchenLatitude: isset($data['kitchen_latitude']) ? (float) $data['kitchen_latitude'] : null,
            kitchenLongitude: isset($data['kitchen_longitude']) ? (float) $data['kitchen_longitude'] : null,
        );
    }
}
