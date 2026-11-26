<?php

namespace App\Infrastructure\Menu\Adapter;

use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;

/**
 * Menu читает филиал через Branch BC.
 */
final class EloquentMenuBranchLookupPort implements MenuBranchLookupPort
{
    public function __construct(
        private readonly BranchRepository $branches,
    ) {}

    public function assertBranchExists(int $branchId): void
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }
    }

    public function assertBranchActive(int $branchId): void
    {
        $branch = $this->branches->findById($branchId);
        if (! $branch instanceof Branch || ! $branch->isActive()) {
            throw new \InvalidArgumentException('Филиал недоступен.');
        }
    }
}
