<?php

namespace App\Domain\Supporting\Company\Entity;

final class BranchCategory
{
    public function __construct(
        private readonly int $id,
        private readonly int $branchId,
        private readonly string $name,
        private readonly string $slug,
        private readonly int $sortOrder,
        private readonly bool $isActive,
        private readonly bool $isAccompanying = false,
        private readonly bool $isComplement = false,
        private readonly bool $isGift = false,
    ) {
        self::assertExclusiveRoles($isAccompanying, $isComplement, $isGift);
    }

    public static function assertExclusiveRoles(
        bool $isAccompanying,
        bool $isComplement,
        bool $isGift,
    ): void {
        $flags = (int) $isAccompanying + (int) $isComplement + (int) $isGift;
        if ($flags > 1) {
            throw new \InvalidArgumentException(
                'У категории может быть только одна роль: accompanying, complement или gift.',
            );
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isAccompanying(): bool
    {
        return $this->isAccompanying;
    }

    public function isComplement(): bool
    {
        return $this->isComplement;
    }

    public function isGift(): bool
    {
        return $this->isGift;
    }
}
