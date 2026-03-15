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

    public static function draft(): self
    {
        return new self('draft');
    }

    public static function confirmed(): self
    {
        return new self('confirmed');
    }

    public static function paid(): self
    {
        return new self('paid');
    }

    public static function shipped(): self
    {
        return new self('shipped');
    }

    public static function canceled(): self
    {
        return new self('canceled');
    }
}

