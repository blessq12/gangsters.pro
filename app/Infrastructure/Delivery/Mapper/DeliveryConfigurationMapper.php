<?php

namespace App\Infrastructure\Delivery\Mapper;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\ValueObject\KitchenAddress;
use App\Infrastructure\Delivery\Model\DLV_Configuration;

final class DeliveryConfigurationMapper
{
    public function toDomain(DLV_Configuration $row): DeliveryConfiguration
    {
        return new DeliveryConfiguration(
            id: (int) $row->id,
            minOrderAmountKopecks: $this->nullableInt($row->min_order_amount_kopecks),
            deliveryFeeKopecks: $this->nullableInt($row->delivery_fee_kopecks),
            outsideZoneDeliveryFeeKopecks: $this->nullableInt($row->outside_zone_delivery_fee_kopecks),
            averageDeliveryTimeMinutes: $this->nullableInt($row->average_delivery_time_minutes),
            kitchenAddress: new KitchenAddress(
                city: $this->nullableString($row->kitchen_city),
                street: $this->nullableString($row->kitchen_street),
                house: $this->nullableString($row->kitchen_house),
                comment: $this->nullableString($row->kitchen_address_comment),
                searchLine: $this->nullableString($row->kitchen_address),
            ),
            kitchenLatitude: $this->nullableFloat($row->kitchen_latitude),
            kitchenLongitude: $this->nullableFloat($row->kitchen_longitude),
            deliveryZoneGeoJson: $this->resolveGeoJson($row->delivery_zone_geojson),
        );
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveGeoJson(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $type = $value['type'] ?? null;
        if (! is_string($type) || ! in_array($type, ['Polygon', 'MultiPolygon'], true)) {
            return null;
        }

        return $value;
    }
}
