<?php

namespace App\Domain\Supporting\Catalog\Entity;

use App\Domain\Supporting\Catalog\Enum\CatalogItemKind;

/**
 * Привязка позиции каталога к категории.
 */
final class CategoryItem
{
    public function __construct(
        private readonly int $categoryId,
        private readonly int $catalogItemId,
        private readonly CatalogItemKind $kind,
        private readonly int $sortOrder,
    ) {}

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function catalogItemId(): int
    {
        return $this->catalogItemId;
    }

    public function kind(): CatalogItemKind
    {
        return $this->kind;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
