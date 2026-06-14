<?php

namespace App\Domain\Client\ValueObject;

final readonly class ClientAddressId
{
    private function __construct(
        private int $value,
    ) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Идентификатор адреса должен быть положительным.');
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
