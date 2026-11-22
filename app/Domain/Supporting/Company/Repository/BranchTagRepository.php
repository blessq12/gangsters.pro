<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\BranchTag;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;

interface BranchTagRepository
{
    /**
     * @return list<BranchTag>
     */
    public function listByBranch(int $branchId): array;

    /**
     * @return list<BranchTag>
     */
    public function listActiveOrderedByBranch(int $branchId): array;

    public function findById(int $branchId, int $id): ?BranchTag;

    public function save(
        int $branchId,
        ?int $id,
        string $code,
        string $label,
        string $color,
        bool $isActive,
        int $sortOrder,
    ): BranchTag;

    public function delete(int $branchId, int $id): void;

    /**
     * @param  list<int>  $tagIds
     */
    public function syncEntryTags(int $branchId, BranchMenuEntryKind $kind, int $entryId, array $tagIds): void;

    /**
     * @return list<int>
     */
    public function findTagIdsForEntry(BranchMenuEntryKind $kind, int $entryId): array;

    /**
     * @param  list<int>  $entryIds
     * @return array<int, list<int>> entryId => tagIds
     */
    public function findTagIdsForEntries(BranchMenuEntryKind $kind, array $entryIds): array;

    /**
     * @param  list<BranchTag>  $tags
     */
    public function replaceAllForBranch(int $branchId, array $tags): array;
}
