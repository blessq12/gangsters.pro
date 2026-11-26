<?php

namespace App\Application\Menu\Command;

use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;

/**
 * Удаление набора филиала вместе с составом, тегами и позициями категорий.
 */
final class DeleteBranchSetUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchSetRepository $sets,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $id): array
    {
        $this->branches->assertBranchExists($branchId);

        $this->sets->delete($branchId, $id);

        return ['ok' => true];
    }
}
