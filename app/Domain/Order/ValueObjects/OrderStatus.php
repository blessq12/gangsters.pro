<?php

namespace App\Domain\Order\ValueObjects;

final class OrderStatus
{
    private function __construct(
        public readonly string $value,
    ) {
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public static function new(): self
    {
        return new self('new');
    }

    public static function preparing(): self
    {
        return new self('preparing');
    }

    public static function inTransit(): self
    {
        return new self('in_transit');
    }

    public static function delivered(): self
    {
        return new self('delivered');
    }
}

