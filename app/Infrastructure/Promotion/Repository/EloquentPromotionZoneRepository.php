<?php

namespace App\Infrastructure\Promotion\Repository;

use App\Domain\Promotion\Entity\PromotionZone;
use App\Domain\Promotion\Repository\PromotionZoneRepository;
use App\Infrastructure\Promotion\Mapper\PromotionZoneMapper;
use App\Infrastructure\Promotion\Model\PRM_Zone;
use Illuminate\Support\Facades\DB;

final class EloquentPromotionZoneRepository implements PromotionZoneRepository
{
    public function __construct(
        private readonly PromotionZoneMapper $mapper,
    ) {}

    public function findAll(): array
    {
        return PRM_Zone::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (PRM_Zone $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findById(string $id): ?PromotionZone
    {
        $row = PRM_Zone::query()->find($id);

        return $row instanceof PRM_Zone ? $this->mapper->toDomain($row) : null;
    }

    public function replaceAll(array $zones): void
    {
        DB::transaction(function () use ($zones): void {
            PRM_Zone::query()->delete();

            foreach ($zones as $zone) {
                if (! $zone instanceof PromotionZone) {
                    continue;
                }

                PRM_Zone::query()->create([
                    'id' => $zone->id(),
                    'name' => $zone->name(),
                    'fee_kopecks' => $zone->feeKopecks(),
                    'is_remote' => $zone->isRemote(),
                ]);
            }
        });
    }
}
