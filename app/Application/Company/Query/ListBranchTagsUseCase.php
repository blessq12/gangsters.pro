<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Company\Entity\BranchTag;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;

/**
 * Теги филиала для админки (включая неактивные).
 */
final class ListBranchTagsUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchTagRepository $tags,
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
                fn (BranchTag $tag): array => $this->presenter->tag($tag),
                $this->tags->listByBranch($branchId),
            ),
        ];
    }
}
