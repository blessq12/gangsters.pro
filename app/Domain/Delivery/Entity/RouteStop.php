<?php

namespace App\Domain\Delivery\Entity;

final class RouteStop
{
    public function __construct(
        private readonly int $orderId,
        private readonly int $position,
        private readonly string $outcome,
        private readonly string $reason,
    ) {}

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function outcome(): string
    {
        return $this->outcome;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
