<?php

namespace App\Application\Menu\Command;

use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;

/**
 * Удаление позиции из меню филиала.
 */
final class DeleteBranchMenuItemUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchMenuRepository $menu,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $catalogItemId): array
    {
        $this->branches->assertBranchExists($branchId);

        $this->menu->delete($branchId, $catalogItemId);

        return ['ok' => true];
    }
}
