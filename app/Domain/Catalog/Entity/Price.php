<?php

namespace App\Domain\Catalog\Entity;

use App\Domain\Catalog\Enum\PriceType;

/**
 * Цена позиции каталога. Одна активная цена на тип (regular / old / promo).
 * Сумма — в копейках.
 */
final class Price
{
    public function __construct(
        private readonly ?int $id,
        private readonly PriceType $type,
        private readonly int $amountKopecks,
    ) {
        if ($this->amountKopecks < 0) {
            throw new \InvalidArgumentException('Цена не может быть отрицательной.');
        }
    }

    public static function of(PriceType $type, int $amountKopecks, ?int $id = null): self
    {
        return new self($id, $type, $amountKopecks);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function type(): PriceType
    {
        return $this->type;
    }

    public function amountKopecks(): int
    {
        return $this->amountKopecks;
    }

    public function amountRubles(): int
    {
        return intdiv($this->amountKopecks, 100);
    }
}
