<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Mapper\DeliveryConfigurationMapper;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use App\Infrastructure\Delivery\Model\DLV_Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentDeliveryConfigurationRepository implements DeliveryConfigurationRepository
{
    public function __construct(
        private readonly DeliveryConfigurationMapper $mapper,
    ) {}

    public function findPublic(): ?DeliveryConfiguration
    {
        $row = DLV_Configuration::query()
            ->with(['zones' => static fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->find(self::SINGLETON_ID);

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
        return DB::transaction(function () use (
            $minOrderAmountKopecks,
            $averageDeliveryTimeMinutes,
            $city,
            $street,
            $house,
            $comment,
            $searchLine,
            $zones,
        ): DeliveryConfiguration {
            $row = DLV_Configuration::query()->find(self::SINGLETON_ID);
            if (! $row instanceof DLV_Configuration) {
                $row = new DLV_Configuration();
                $row->id = self::SINGLETON_ID;
            }

            $row->fill([
                'min_order_amount_kopecks' => $minOrderAmountKopecks,
                'average_delivery_time_minutes' => $averageDeliveryTimeMinutes,
                'kitchen_address' => [
                    'city' => $city,
                    'street' => $street,
                    'house' => $house,
                    'comment' => $comment,
                    'search_line' => $searchLine,
                ],
            ]);
            $row->save();

            $existing = DLV_Zone::query()
                ->where('configuration_id', $row->id)
                ->get()
                ->keyBy(static fn (DLV_Zone $zone): string => (string) $zone->zone_key);

            $keptKeys = [];
            $sortOrder = 0;

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

                if ($id !== '' && $existing->has($id)) {
                    /** @var DLV_Zone $zoneRow */
                    $zoneRow = $existing->get($id);
                    $zoneRow->fill([
                        'name' => $name !== '' ? $name : (string) $zoneRow->name,
                        'fee_kopecks' => $feeRubles * 100,
                        'is_remote' => $isRemote,
                        'sort_order' => $sortOrder,
                    ]);
                    if ($hasGeometry) {
                        $zoneRow->geojson = $geometry;
                    }
                    $zoneRow->save();
                    $keptKeys[$id] = true;
                    $sortOrder++;

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
                DLV_Zone::query()->create([
                    'configuration_id' => (int) $row->id,
                    'zone_key' => $newId,
                    'name' => $name,
                    'fee_kopecks' => $feeRubles * 100,
                    'is_remote' => $isRemote,
                    'geojson' => $geometry,
                    'delivery_product_id' => null,
                    'sort_order' => $sortOrder,
                ]);
                $keptKeys[$newId] = true;
                $sortOrder++;
            }

            foreach ($existing as $zoneKey => $zoneRow) {
                if (! isset($keptKeys[$zoneKey])) {
                    $zoneRow->delete();
                }
            }

            $row->load(['zones' => static fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

            return $this->mapper->toDomain($row);
        });
    }
}
