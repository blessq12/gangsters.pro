<?php

namespace App\Domain\Order\Event;

use DateTimeImmutable;

/** Заказ принят (подтверждён); на него реагирует Production — ставит тикет в очередь. */
final readonly class OrderAccepted
{
    public function __construct(
        private int $orderId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
