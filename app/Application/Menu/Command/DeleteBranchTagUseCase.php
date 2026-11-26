<?php

namespace App\Application\Menu\Command;

use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;

/**
 * Удаление тега филиала вместе с его привязками.
 */
final class DeleteBranchTagUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchTagRepository $tags,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $id): array
    {
        $this->branches->assertBranchExists($branchId);

        $this->tags->delete($branchId, $id);

        return ['ok' => true];
    }
}
