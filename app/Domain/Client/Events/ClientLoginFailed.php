<?php

namespace App\Domain\Client\Events;

use App\Shared\Events\DomainEvent;
use DateTimeImmutable;

final class ClientLoginFailed implements DomainEvent
{
    public function __construct(
        private readonly string $identifier,
        private readonly string $reason,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}

