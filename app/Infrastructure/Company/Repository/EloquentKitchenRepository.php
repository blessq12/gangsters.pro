<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Domain\Supporting\Company\Repository\KitchenRepository;
use App\Infrastructure\Company\Mapper\KitchenMapper;
use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Company\Model\CMP_Kitchen;

final class EloquentKitchenRepository implements KitchenRepository
{
    public function __construct(
        private readonly KitchenMapper $mapper,
    ) {}

    public function findById(int $id): ?Kitchen
    {
        $row = CMP_Kitchen::query()->find($id);

        return $row instanceof CMP_Kitchen ? $this->mapper->toDomain($row) : null;
    }

    public function listByCompany(int $companyId): array
    {
        $branchIds = CMP_Branch::query()
            ->where('company_id', $companyId)
            ->pluck('id')
            ->all();

        if ($branchIds === []) {
            return [];
        }

        return CMP_Kitchen::query()
            ->whereIn('branch_id', $branchIds)
            ->orderBy('id')
            ->get()
            ->map(fn (CMP_Kitchen $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(Kitchen $kitchen): Kitchen
    {
        if ($kitchen->id() < 1) {
            $row = new CMP_Kitchen();
        } else {
            $row = CMP_Kitchen::query()->find($kitchen->id());
            if (! $row instanceof CMP_Kitchen) {
                throw new \InvalidArgumentException('Кухня не найдена.');
            }
        }

        if (! CMP_Branch::query()->whereKey($kitchen->branchId())->exists()) {
            throw new \InvalidArgumentException('Филиал для кухни не найден.');
        }

        $row->fill([
            'branch_id' => $kitchen->branchId(),
            'city' => $kitchen->city(),
            'street' => $kitchen->street(),
            'house' => $kitchen->house(),
            'comment' => $kitchen->comment(),
            'latitude' => $kitchen->latitude(),
            'longitude' => $kitchen->longitude(),
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
