<?php

namespace App\Filament\Operations\Support;

use App\Application\Operations\Delivery\DTO\UpdateDeliverySettingsDto;

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
            'delivery_hours' => $detail['delivery_hours'] ?? null,
            'min_order_amount_kopecks' => $detail['min_order_amount_kopecks'] ?? null,
            'delivery_fee_kopecks' => $detail['delivery_fee_kopecks'] ?? null,
            'average_delivery_time_minutes' => $detail['average_delivery_time_minutes'] ?? null,
            'delivery_zone_geojson' => $detail['delivery_zone_geojson'] ?? null,
            'kitchen_address' => '',
            'kitchen_latitude' => $detail['kitchen_latitude'] ?? null,
            'kitchen_longitude' => $detail['kitchen_longitude'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): UpdateDeliverySettingsDto
    {
        return new UpdateDeliverySettingsDto(
            deliveryZoneGeojson: $data['delivery_zone_geojson'] ?? null,
            kitchenLatitude: isset($data['kitchen_latitude']) ? (float) $data['kitchen_latitude'] : null,
            kitchenLongitude: isset($data['kitchen_longitude']) ? (float) $data['kitchen_longitude'] : null,
            deliveryHours: $data['delivery_hours'] ?? null,
            minOrderAmountKopecks: isset($data['min_order_amount_kopecks'])
                ? (int) $data['min_order_amount_kopecks']
                : null,
            deliveryFeeKopecks: isset($data['delivery_fee_kopecks'])
                ? (int) $data['delivery_fee_kopecks']
                : null,
            averageDeliveryTimeMinutes: isset($data['average_delivery_time_minutes'])
                ? (int) $data['average_delivery_time_minutes']
                : null,
        );
    }
}
