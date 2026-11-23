<?php

namespace App\Application\Company\Command;

use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Удаление категории филиала вместе с её позициями.
 */
final class DeleteBranchCategoryUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchCategoryRepository $categories,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $id): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $this->categories->delete($branchId, $id);

        return ['ok' => true];
    }
}
