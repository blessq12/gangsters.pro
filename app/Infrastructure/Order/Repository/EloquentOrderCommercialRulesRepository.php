<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Core\Order\Entity\OrderCommercialRules;
use App\Domain\Core\Order\Repository\OrderCommercialRulesRepository;
use App\Infrastructure\Order\Mapper\OrderCommercialRulesMapper;
use App\Infrastructure\Order\Model\BranchCommercialRulesModel;

final class EloquentOrderCommercialRulesRepository implements OrderCommercialRulesRepository
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
