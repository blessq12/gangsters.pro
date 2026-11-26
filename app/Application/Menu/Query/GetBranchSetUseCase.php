<?php

namespace App\Application\Menu\Query;

use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;

/**
 * Один набор филиала.
 */
final class GetBranchSetUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId, int $id): array
    {
        $this->branches->assertBranchExists($branchId);

        $set = $this->sets->findById($branchId, $id);
        if ($set === null) {
            throw new \InvalidArgumentException('Набор не найден.');
        }

        return $this->presenter->set($set);
    }
}
