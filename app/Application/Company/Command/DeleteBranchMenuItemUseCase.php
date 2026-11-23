<?php

namespace App\Application\Company\Command;

use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Удаление позиции из меню филиала.
 */
final class DeleteBranchMenuItemUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchMenuRepository $menu,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $catalogItemId): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $this->menu->delete($branchId, $catalogItemId);

        return ['ok' => true];
    }
}
