<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;

/**
 * Один набор филиала.
 */
final class GetBranchSetUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId, int $id): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $set = $this->sets->findById($branchId, $id);
        if ($set === null) {
            throw new \InvalidArgumentException('Набор не найден.');
        }

        return $this->presenter->set($set);
    }
}
