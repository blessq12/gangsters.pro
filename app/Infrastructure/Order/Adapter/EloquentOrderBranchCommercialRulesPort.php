<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Order\Entity\OrderCommercialRules;
use App\Domain\Core\Order\Port\OrderBranchCommercialRulesPort;
use App\Domain\Supporting\Branch\Repository\BranchCommercialRulesRepository;

/**
 * ACL: Order читает снимок commercial rules; SoT в Branch.
 */
final class EloquentOrderBranchCommercialRulesPort implements OrderBranchCommercialRulesPort
{
    public function __construct(
        private readonly BranchCommercialRulesRepository $rules,
    ) {}

    public function findByBranch(int $branchId): ?OrderCommercialRules
    {
        return $this->rules->findByBranch($branchId);
    }
}
