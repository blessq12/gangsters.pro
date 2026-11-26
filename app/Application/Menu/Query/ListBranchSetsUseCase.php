<?php

namespace App\Application\Menu\Query;

use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Entity\BranchSet;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;

/**
 * Наборы филиала для админки (включая недоступные).
 */
final class ListBranchSetsUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuStructurePresenter $presenter,
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
                fn (BranchSet $set): array => $this->presenter->set($set),
                $this->sets->listByBranch($branchId),
            ),
        ];
    }
}
