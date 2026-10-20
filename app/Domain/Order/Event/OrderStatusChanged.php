<?php

namespace App\Domain\Order\Event;

use DateTimeImmutable;

final readonly class OrderStatusChanged
{
    public function __construct(
        private int $orderId,
        private string $previousStatus,
        private string $newStatus,
        private string $source,
        private ?DateTimeImmutable $changedAt,
    ) {}

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function previousStatus(): string
    {
        return $this->previousStatus;
    }

    public function newStatus(): string
    {
        return $this->newStatus;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function changedAt(): ?DateTimeImmutable
    {
        return $this->changedAt;
    }
}
