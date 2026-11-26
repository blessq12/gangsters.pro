<?php

namespace App\Application\Menu\Command;

use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;

/**
 * Удаление категории филиала вместе с её позициями.
 */
final class DeleteBranchCategoryUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchCategoryRepository $categories,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $id): array
    {
        $this->branches->assertBranchExists($branchId);

        $this->categories->delete($branchId, $id);

        return ['ok' => true];
    }
}
