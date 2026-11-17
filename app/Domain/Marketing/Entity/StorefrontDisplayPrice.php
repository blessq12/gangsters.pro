<?php

namespace App\Domain\Marketing\Entity;

/**
 * Витринная «старая» цена позиции каталога (зачёркнутая). Владелец — Marketing.
 */
final class StorefrontDisplayPrice
{
    public function __construct(
        private readonly int $catalogItemId,
        private readonly ?int $oldPriceKopecks,
    ) {}

    public function catalogItemId(): int
    {
        return $this->catalogItemId;
    }

    public function oldPriceKopecks(): ?int
    {
        return $this->oldPriceKopecks;
    }
}
