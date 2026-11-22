<?php

namespace App\Domain\Supporting\Company\Entity;

/**
 * Позиция меню филиала: доступность и продажная цена catalog item в контексте branchId.
 * Каталог остаётся справочником без знания о филиалах.
 */
final class BranchMenuItem
{
    public function __construct(
        private readonly int $branchId,
        private readonly int $catalogItemId,
        private readonly bool $isAvailable,
        private readonly int $priceKopecks,
        private readonly ?int $id = null,
    ) {
        if ($this->branchId < 1) {
            throw new \InvalidArgumentException('branch_id должен быть положительным.');
        }
        if ($this->catalogItemId < 1) {
            throw new \InvalidArgumentException('catalog_item_id должен быть положительным.');
        }
        if ($this->priceKopecks < 0) {
            throw new \InvalidArgumentException('Цена меню не может быть отрицательной.');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function catalogItemId(): int
    {
        return $this->catalogItemId;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function priceKopecks(): int
    {
        return $this->priceKopecks;
    }
}
