<?php

namespace App\Domain\Core\Production\ValueObject;

/**
 * Снимок позиции для кухни: что именно готовить.
 */
final readonly class KitchenTicketLine
{
    public function __construct(
        private int $productId,
        private string $name,
        private int $quantity,
    ) {
        if ($this->productId < 1) {
            throw new \InvalidArgumentException('productId должен быть положительным.');
        }

        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Название позиции тикета обязательно.');
        }

        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('Количество в тикете должно быть не меньше 1.');
        }
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
