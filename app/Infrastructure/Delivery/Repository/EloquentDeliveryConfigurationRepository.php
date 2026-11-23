<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;
use App\Infrastructure\Delivery\Mapper\DeliveryConfigurationMapper;
use App\Infrastructure\Delivery\Model\DeliveryConfigurationModel;
use App\Infrastructure\Delivery\Model\DeliveryZoneModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentDeliveryConfigurationRepository implements DeliveryConfigurationRepository
{
    public function __construct(
        private readonly DeliveryConfigurationMapper $mapper,
    ) {}

    public function findByBranch(int $branchId): ?DeliveryConfiguration
    {
        $row = DeliveryConfigurationModel::query()
            ->with(['zones' => static fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->where('branch_id', $branchId)
            ->first();

        return $row instanceof DeliveryConfigurationModel ? $this->mapper->toDomain($row) : null;
    }

    public function listZonesPageByBranch(int $branchId, int $offset, int $limit): array
    {
        $zones = [];
        $rows = DeliveryZoneModel::query()
            ->whereIn('configuration_id', $this->configurationIdsByBranch($branchId))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get();

        foreach ($rows as $row) {
            $zone = $this->mapper->zoneToDomain($row);
            if ($zone instanceof DeliveryZone) {
                $zones[] = $zone;
            }
        }

        return $zones;
    }

    public function countZonesByBranch(int $branchId): int
    {
        return (int) DeliveryZoneModel::query()
            ->whereIn('configuration_id', $this->configurationIdsByBranch($branchId))
            ->count();
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
    public function saveSettingsForBranch(
        int $branchId,
        ?int $minOrderAmountKopecks,
        ?int $averageDeliveryTimeMinutes,
        array $zones,
    ): DeliveryConfiguration {
        return DB::transaction(function () use (
            $branchId,
            $minOrderAmountKopecks,
            $averageDeliveryTimeMinutes,
            $zones,
        ): DeliveryConfiguration {
            $row = DeliveryConfigurationModel::query()->where('branch_id', $branchId)->first();
            if (! $row instanceof DeliveryConfigurationModel) {
                $row = new DeliveryConfigurationModel();
            }

            $row->fill([
                'min_order_amount_kopecks' => $minOrderAmountKopecks,
                'average_delivery_time_minutes' => $averageDeliveryTimeMinutes,
                'branch_id' => $branchId,
            ]);
            $row->save();

            $existing = DeliveryZoneModel::query()
                ->where('configuration_id', $row->id)
                ->get()
                ->keyBy(static fn (DeliveryZoneModel $zone): string => (string) $zone->zone_key);

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
                    /** @var DeliveryZoneModel $zoneRow */
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
                DeliveryZoneModel::query()->create([
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

    /**
     * Id конфигураций филиала (одна конфигурация на филиал).
     *
     * @return list<int>
     */
    private function configurationIdsByBranch(int $branchId): array
    {
        return DeliveryConfigurationModel::query()
            ->where('branch_id', $branchId)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();
    }
}
