<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Mapper\DeliveryConfigurationMapper;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use Illuminate\Support\Str;

final class EloquentDeliveryConfigurationRepository implements DeliveryConfigurationRepository
{
    public function __construct(
        private readonly DeliveryConfigurationMapper $mapper,
    ) {}

    public function findPublic(): ?DeliveryConfiguration
    {
        $row = DLV_Configuration::query()->find(self::SINGLETON_ID);

        return $row instanceof DLV_Configuration ? $this->mapper->toDomain($row) : null;
    }

    /**
     * @param  list<array{
     *     id?: string,
     *     name: string,
     *     fee_rubles: int,
     *     is_remote: bool,
     *     geometry?: array<string, mixed>|null
     * }>  $zones
     */
    public function saveSettings(
        ?int $minOrderAmountKopecks,
        ?int $averageDeliveryTimeMinutes,
        ?string $city,
        ?string $street,
        ?string $house,
        ?string $comment,
        ?string $searchLine,
        array $zones,
    ): DeliveryConfiguration {
        $row = DLV_Configuration::query()->find(self::SINGLETON_ID);
        if (! $row instanceof DLV_Configuration) {
            throw new \InvalidArgumentException('Настройки доставки не найдены.');
        }

        $stored = is_array($row->delivery_zones) ? $row->delivery_zones : [];
        $indexed = [];
        foreach ($stored as $storedZone) {
            if (! is_array($storedZone)) {
                continue;
            }
            $id = trim((string) ($storedZone['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $indexed[$id] = $storedZone;
        }

        foreach ($zones as $zone) {
            if (! is_array($zone)) {
                continue;
            }

            $id = trim((string) ($zone['id'] ?? ''));
            $name = trim((string) ($zone['name'] ?? ''));
            $feeRubles = max(0, (int) ($zone['fee_rubles'] ?? 0));
            $isRemote = (bool) ($zone['is_remote'] ?? false);
            $geometry = $zone['geometry'] ?? null;
            $hasGeometry = is_array($geometry);

            if ($id !== '' && isset($indexed[$id])) {
                $indexed[$id]['name'] = $name !== '' ? $name : (string) ($indexed[$id]['name'] ?? 'Зона');
                $indexed[$id]['delivery_fee_kopecks'] = $feeRubles * 100;
                $indexed[$id]['is_remote'] = $isRemote;
                if ($hasGeometry) {
                    $indexed[$id]['geometry'] = $geometry;
                }
                continue;
            }

            if (! $hasGeometry) {
                throw new \InvalidArgumentException(
                    $id === ''
                        ? 'Для новой зоны нужен geometry (GeoJSON).'
                        : 'В списке есть зона, которой нет на карте.',
                );
            }

            if ($name === '') {
                throw new \InvalidArgumentException('У новой зоны нужно название.');
            }

            $newId = $id !== '' ? $id : (string) Str::uuid();
            $indexed[$newId] = [
                'id' => $newId,
                'name' => $name,
                'delivery_fee_kopecks' => $feeRubles * 100,
                'is_remote' => $isRemote,
                'geometry' => $geometry,
                'delivery_product_id' => null,
            ];
        }

        $merged = array_values($indexed);

        $row->fill([
            'min_order_amount_kopecks' => $minOrderAmountKopecks,
            'average_delivery_time_minutes' => $averageDeliveryTimeMinutes,
            'kitchen_city' => $city,
            'kitchen_street' => $street,
            'kitchen_house' => $house,
            'kitchen_address_comment' => $comment,
            'kitchen_address' => $searchLine,
            'delivery_zones' => $merged,
            'delivery_zone_geojson' => $this->buildUnionGeoJson($merged),
            'delivery_fee_kopecks' => $this->minFeeKopecks($merged),
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }

    /**
     * @param  list<array<string, mixed>>  $zones
     * @return array<string, mixed>|null
     */
    private function buildUnionGeoJson(array $zones): ?array
    {
        $polygons = [];
        foreach ($zones as $zone) {
            $geometry = is_array($zone['geometry'] ?? null) ? $zone['geometry'] : null;
            if ($geometry === null) {
                continue;
            }
            $type = $geometry['type'] ?? null;
            if ($type === 'Polygon') {
                $polygons[] = $geometry['coordinates'] ?? [];
            } elseif ($type === 'MultiPolygon') {
                foreach ($geometry['coordinates'] ?? [] as $polygonCoordinates) {
                    $polygons[] = $polygonCoordinates;
                }
            }
        }

        if ($polygons === []) {
            return null;
        }

        if (count($polygons) === 1) {
            return [
                'type' => 'Polygon',
                'coordinates' => $polygons[0],
            ];
        }

        return [
            'type' => 'MultiPolygon',
            'coordinates' => $polygons,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $zones
     */
    private function minFeeKopecks(array $zones): ?int
    {
        if ($zones === []) {
            return null;
        }

        $fees = [];
        foreach ($zones as $zone) {
            $fees[] = max(0, (int) ($zone['delivery_fee_kopecks'] ?? 0));
        }

        return min($fees);
    }
}
