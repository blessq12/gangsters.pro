<?php

namespace App\Application\Menu\Query;

use App\Application\Menu\Presenter\BranchMenuPresenter;
use App\Domain\Supporting\Menu\Entity\BranchMenuItem;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;

/**
 * Меню филиала для админки: все позиции, включая недоступные.
 */
final class GetAdminBranchMenuUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchMenuRepository $menu,
        private readonly BranchMenuPresenter $presenter,
    ) {}

    /**
     * @return array{branch_id: int, items: list<array<string, mixed>>}
     */
    public function execute(int $branchId): array
    {
        $this->branches->assertBranchExists($branchId);

        return [
            'branch_id' => $branchId,
            'items' => array_map(
                fn (BranchMenuItem $item): array => $this->presenter->item($item),
                $this->menu->listByBranch($branchId),
            ),
        ];
    }
}
