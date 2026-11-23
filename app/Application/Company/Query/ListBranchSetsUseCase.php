<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;

/**
 * Наборы филиала для админки (включая недоступные).
 */
final class ListBranchSetsUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuStructurePresenter $presenter,
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
                fn (BranchSet $set): array => $this->presenter->set($set),
                $this->sets->listByBranch($branchId),
            ),
        ];
    }
}
