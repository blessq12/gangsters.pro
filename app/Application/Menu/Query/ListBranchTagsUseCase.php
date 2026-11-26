<?php

namespace App\Application\Menu\Query;

use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Entity\BranchTag;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;

/**
 * Теги филиала для админки (включая неактивные).
 */
final class ListBranchTagsUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchTagRepository $tags,
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
                fn (BranchTag $tag): array => $this->presenter->tag($tag),
                $this->tags->listByBranch($branchId),
            ),
        ];
    }
}
