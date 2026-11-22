<?php

namespace App\Infrastructure\Promotion\Repository;

use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;
use App\Infrastructure\Promotion\Mapper\PromotionZoneMapper;
use App\Infrastructure\Promotion\Model\PRM_Zone;
use Illuminate\Support\Facades\DB;

final class EloquentPromotionZoneRepository implements PromotionZoneRepository
{
    public function __construct(
        private readonly PromotionZoneMapper $mapper,
    ) {}

    public function findAllByBranch(int $branchId): array
    {
        return PRM_Zone::query()
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (PRM_Zone $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listPageByBranch(int $branchId, int $offset, int $limit): array
    {
        return PRM_Zone::query()
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (PRM_Zone $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countByBranch(int $branchId): int
    {
        return (int) PRM_Zone::query()
            ->where('branch_id', $branchId)
            ->count();
    }

    public function findByBranchAndId(int $branchId, string $id): ?PromotionZone
    {
        $row = PRM_Zone::query()
            ->where('branch_id', $branchId)
            ->where('id', $id)
            ->first();

        return $row instanceof PRM_Zone ? $this->mapper->toDomain($row) : null;
    }

    public function replaceForBranch(int $branchId, array $zones): void
    {
        DB::transaction(function () use ($branchId, $zones): void {
            PRM_Zone::query()->where('branch_id', $branchId)->delete();

            foreach ($zones as $zone) {
                if (! $zone instanceof PromotionZone) {
                    continue;
                }

                PRM_Zone::query()->create([
                    'id' => $zone->id(),
                    'branch_id' => $branchId,
                    'name' => $zone->name(),
                    'fee_kopecks' => $zone->feeKopecks(),
                    'is_remote' => $zone->isRemote(),
                ]);
            }
        });
    }
}
