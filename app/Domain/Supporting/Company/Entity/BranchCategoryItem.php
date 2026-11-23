<?php

namespace App\Domain\Supporting\Company\Entity;

use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;

final class BranchCategoryItem
{
    public function __construct(
        private readonly int $categoryId,
        private readonly BranchMenuEntryKind $entryKind,
        private readonly int $entryId,
        private readonly int $sortOrder,
        private readonly ?int $id = null,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function entryKind(): BranchMenuEntryKind
    {
        return $this->entryKind;
    }

    public function entryId(): int
    {
        return $this->entryId;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
