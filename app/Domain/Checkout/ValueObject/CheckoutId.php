<?php

namespace App\Domain\Checkout\ValueObject;

use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class CheckoutId
{
    public function __construct(
        private string $value,
    ) {
        if (! Str::isUuid($this->value)) {
            throw new InvalidArgumentException('Некорректный идентификатор чекаута.');
        }
    }

    public static function generate(): self
    {
        return new self((string) Str::uuid());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
