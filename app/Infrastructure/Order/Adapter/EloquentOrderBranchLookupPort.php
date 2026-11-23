<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Order\Port\OrderBranchLookupPort;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * ACL: Order проверяет активный филиал через BranchRepository в Infrastructure.
 */
final class EloquentOrderBranchLookupPort implements OrderBranchLookupPort
{
    public function __construct(
        private readonly BranchRepository $branches,
    ) {}

    public function requireActiveBranch(int $branchId): array
    {
        $branch = $this->branches->findById($branchId);
        if (! $branch instanceof Branch || ! $branch->isActive()) {
            throw new \InvalidArgumentException('Филиал недоступен для оформления заказа.');
        }

        return [
            'id' => $branch->id(),
            'city' => $branch->city(),
        ];
    }
}
