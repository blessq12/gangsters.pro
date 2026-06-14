<?php

namespace App\Domain\Catalog\Contract;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;

/**
 * Общая плоскость витрины: товар и набор — равноправные позиции каталога.
 */
interface CatalogItem
{
    public function id(): int;

    public function kind(): CatalogItemKind;

    public function name(): string;

    public function slug(): string;

    public function status(): ProductStatus;

    public function isActive(): bool;
}
