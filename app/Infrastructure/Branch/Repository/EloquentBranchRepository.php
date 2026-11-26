<?php

namespace App\Infrastructure\Branch\Repository;

use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Infrastructure\Branch\Mapper\BranchMapper;
use App\Infrastructure\Branch\Model\BranchModel;

final class EloquentBranchRepository implements BranchRepository
{
    public function __construct(
        private readonly BranchMapper $mapper,
    ) {}

    public function findById(int $id): ?Branch
    {
        $row = BranchModel::query()->find($id);

        return $row instanceof BranchModel ? $this->mapper->toDomain($row) : null;
    }

    public function listByCompany(int $companyId): array
    {
        return BranchModel::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get()
            ->map(fn (BranchModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listPageByCompany(int $companyId, int $offset, int $limit): array
    {
        return BranchModel::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (BranchModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countByCompany(int $companyId): int
    {
        return (int) BranchModel::query()->where('company_id', $companyId)->count();
    }

    public function save(Branch $branch): Branch
    {
        if ($branch->id() < 1) {
            $row = new BranchModel;
        } else {
            $row = BranchModel::query()->find($branch->id());
            if (! $row instanceof BranchModel) {
                throw new \InvalidArgumentException('Филиал не найден.');
            }
        }

        $row->fill([
            'company_id' => $branch->companyId(),
            'name' => $branch->name(),
            'is_active' => $branch->isActive(),
            'schedule' => $branch->schedule(),
            'city' => $branch->city(),
            'street' => $branch->street(),
            'house' => $branch->house(),
            'comment' => $branch->comment(),
            'latitude' => $branch->latitude(),
            'longitude' => $branch->longitude(),
            'phone' => $branch->phone(),
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
