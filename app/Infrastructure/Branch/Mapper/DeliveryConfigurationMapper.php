<?php

namespace App\Infrastructure\Branch\Mapper;

use App\Domain\Supporting\Branch\Entity\DeliveryConfiguration;
use App\Domain\Supporting\Branch\ValueObject\DeliveryZone;
use App\Infrastructure\Branch\Model\DeliveryConfigurationModel;
use App\Infrastructure\Branch\Model\DeliveryZoneModel;

final class DeliveryConfigurationMapper
{
    public function toDomain(DeliveryConfigurationModel $row): DeliveryConfiguration
    {
        return new DeliveryConfiguration(
            id: (int) $row->id,
            minOrderAmountKopecks: $this->nullableInt($row->min_order_amount_kopecks),
            averageDeliveryTimeMinutes: $this->nullableInt($row->average_delivery_time_minutes),
            branchId: $this->nullableInt($row->branch_id),
            zones: $this->resolveZones($row),
        );
    }

    /**
     * @return list<DeliveryZone>
     */
    private function resolveZones(DeliveryConfigurationModel $row): array
    {
        $zones = [];

        $zoneRows = $row->relationLoaded('zones')
            ? $row->zones
            : DeliveryZoneModel::query()
                ->where('configuration_id', $row->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

        foreach ($zoneRows as $zoneRow) {
            if (! $zoneRow instanceof DeliveryZoneModel) {
                continue;
            }

            $zone = $this->mapZoneRow($zoneRow);
            if ($zone instanceof DeliveryZone) {
                $zones[] = $zone;
            }
        }

        return $zones;
    }

    public function zoneToDomain(DeliveryZoneModel $row): ?DeliveryZone
    {
        return $this->mapZoneRow($row);
    }

    private function mapZoneRow(DeliveryZoneModel $row): ?DeliveryZone
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

        $int = (int) $value;

        return $int > 0 ? $int : null;
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
