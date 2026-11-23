<?php

namespace App\Application\Company\Command;

use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;

/**
 * Удаление набора филиала вместе с составом, тегами и позициями категорий.
 */
final class DeleteBranchSetUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchSetRepository $sets,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $id): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $this->sets->delete($branchId, $id);

        return ['ok' => true];
    }
}
