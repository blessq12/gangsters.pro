<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\BranchMenuPresenter;
use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Меню филиала для админки: все позиции, включая недоступные.
 */
final class GetAdminBranchMenuUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchMenuRepository $menu,
        private readonly BranchMenuPresenter $presenter,
    ) {}

    /**
     * @return array{branch_id: int, items: list<array<string, mixed>>}
     */
    public function execute(int $branchId): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        return [
            'branch_id' => $branchId,
            'items' => array_map(
                fn (BranchMenuItem $item): array => $this->presenter->item($item),
                $this->menu->listByBranch($branchId),
            ),
        ];
    }
}
