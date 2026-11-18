<?php

namespace App\Infrastructure\Delivery\Mapper;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\ValueObject\DeliveryZone;
use App\Domain\Delivery\ValueObject\KitchenAddress;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use App\Infrastructure\Delivery\Model\DLV_Zone;

final class DeliveryConfigurationMapper
{
    public function toDomain(DLV_Configuration $row): DeliveryConfiguration
    {
        $address = is_array($row->kitchen_address) ? $row->kitchen_address : [];

        return new DeliveryConfiguration(
            id: (int) $row->id,
            minOrderAmountKopecks: $this->nullableInt($row->min_order_amount_kopecks),
            averageDeliveryTimeMinutes: $this->nullableInt($row->average_delivery_time_minutes),
            kitchenAddress: new KitchenAddress(
                city: $this->nullableString($address['city'] ?? null),
                street: $this->nullableString($address['street'] ?? null),
                house: $this->nullableString($address['house'] ?? null),
                comment: $this->nullableString($address['comment'] ?? null),
                searchLine: $this->nullableString($address['search_line'] ?? null),
            ),
            kitchenLatitude: $this->nullableFloat($row->kitchen_latitude),
            kitchenLongitude: $this->nullableFloat($row->kitchen_longitude),
            zones: $this->resolveZones($row),
        );
    }

    /**
     * @return list<DeliveryZone>
     */
    private function resolveZones(DLV_Configuration $row): array
    {
        $zones = [];

        $zoneRows = $row->relationLoaded('zones')
            ? $row->zones
            : DLV_Zone::query()
                ->where('configuration_id', $row->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

        foreach ($zoneRows as $zoneRow) {
            if (! $zoneRow instanceof DLV_Zone) {
                continue;
            }

            $zone = $this->mapZoneRow($zoneRow);
            if ($zone instanceof DeliveryZone) {
                $zones[] = $zone;
            }
        }

        return $zones;
    }

    private function mapZoneRow(DLV_Zone $row): ?DeliveryZone
    {
        $geometry = $this->resolveGeoJson($row->geojson);
        if ($geometry === null) {
            return null;
        }

        $deliveryProductId = $row->delivery_product_id !== null
            ? (int) $row->delivery_product_id
            : 0;

        return new DeliveryZone(
            id: (string) $row->zone_key,
            name: (string) $row->name,
            deliveryFeeKopecks: max(0, (int) $row->fee_kopecks),
            isRemote: (bool) $row->is_remote,
            geometry: $geometry,
            deliveryProductId: $deliveryProductId > 0 ? $deliveryProductId : null,
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
