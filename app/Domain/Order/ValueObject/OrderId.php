<?php

namespace App\Domain\Order\ValueObject;

final readonly class OrderId
{
    private function __construct(
        private int $value,
    ) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Идентификатор заказа должен быть положительным.');
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
