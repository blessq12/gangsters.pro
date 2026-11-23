<?php

namespace App\Application\Company\Command;

use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;

/**
 * Удаление тега филиала вместе с его привязками.
 */
final class DeleteBranchTagUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchTagRepository $tags,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $branchId, int $id): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $this->tags->delete($branchId, $id);

        return ['ok' => true];
    }
}
