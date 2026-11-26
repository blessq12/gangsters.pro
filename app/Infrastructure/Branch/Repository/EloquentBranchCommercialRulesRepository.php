<?php

namespace App\Infrastructure\Branch\Repository;

use App\Domain\Core\Order\Entity\OrderCommercialRules;
use App\Domain\Supporting\Branch\Repository\BranchCommercialRulesRepository;
use App\Infrastructure\Branch\Mapper\OrderCommercialRulesMapper;
use App\Infrastructure\Branch\Model\BranchCommercialRulesModel;

final class EloquentBranchCommercialRulesRepository implements BranchCommercialRulesRepository
{
    public function __construct(
        private readonly OrderCommercialRulesMapper $mapper,
    ) {}

    public function findByBranch(int $branchId): ?OrderCommercialRules
    {
        $row = BranchCommercialRulesModel::query()->where('branch_id', $branchId)->first();

        return $row instanceof BranchCommercialRulesModel ? $this->mapper->toDomain($row) : null;
    }

    public function saveForBranch(int $branchId, array $fields): OrderCommercialRules
    {
        $row = BranchCommercialRulesModel::query()->where('branch_id', $branchId)->first();
        if (! $row instanceof BranchCommercialRulesModel) {
            $row = new BranchCommercialRulesModel;
            $row->branch_id = $branchId;
        }

        $row->fill($this->mapper->flatFieldsToPersistence($fields));
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
