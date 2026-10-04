<?php

namespace App\Infrastructure\Content\Mapper;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\ValueObject\DeliveryZone;
use App\Domain\Content\ValueObject\KitchenAddress;
use App\Infrastructure\Content\Model\DLV_Configuration;
use Illuminate\Support\Str;

final class DeliveryConfigurationMapper
{
    public function toDomain(DLV_Configuration $row): DeliveryConfiguration
    {
        return new DeliveryConfiguration(
            id: (int) $row->id,
            minOrderAmountKopecks: $this->nullableInt($row->min_order_amount_kopecks),
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
            zones: $this->resolveZones($row),
        );
    }

    /**
     * @return list<DeliveryZone>
     */
    private function resolveZones(DLV_Configuration $row): array
    {
        $rawZones = $row->delivery_zones;
        if (is_array($rawZones) && $rawZones !== []) {
            $zones = [];
            foreach ($rawZones as $rawZone) {
                $zone = $this->mapZone($rawZone);
                if ($zone instanceof DeliveryZone) {
                    $zones[] = $zone;
                }
            }

            if ($zones !== []) {
                return $zones;
            }
        }

        // Legacy fallback: один полигон + скалярный тариф.
        $geometry = $this->resolveGeoJson($row->delivery_zone_geojson);
        if ($geometry === null) {
            return [];
        }

        return [
            new DeliveryZone(
                id: (string) Str::uuid(),
                name: 'Основная',
                deliveryFeeKopecks: max(0, (int) ($row->delivery_fee_kopecks ?? 0)),
                isRemote: false,
                geometry: $geometry,
            ),
        ];
    }

    private function mapZone(mixed $raw): ?DeliveryZone
    {
        if (! is_array($raw)) {
            return null;
        }

        $geometry = $this->resolveGeoJson($raw['geometry'] ?? null);
        if ($geometry === null) {
            return null;
        }

        $id = trim((string) ($raw['id'] ?? ''));
        if ($id === '') {
            $id = (string) Str::uuid();
        }

        $name = trim((string) ($raw['name'] ?? ''));
        if ($name === '') {
            $name = 'Зона';
        }

        $deliveryProductId = isset($raw['delivery_product_id'])
            ? (int) $raw['delivery_product_id']
            : 0;

        return new DeliveryZone(
            id: $id,
            name: $name,
            deliveryFeeKopecks: max(0, (int) ($raw['delivery_fee_kopecks'] ?? 0)),
            isRemote: (bool) ($raw['is_remote'] ?? false),
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
