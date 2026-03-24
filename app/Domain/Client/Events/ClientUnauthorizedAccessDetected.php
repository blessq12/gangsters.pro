<?php

namespace App\Domain\Client\Events;

use App\Shared\Events\DomainEvent;
use DateTimeImmutable;

final class ClientUnauthorizedAccessDetected implements DomainEvent
{
    public function __construct(
        private readonly string $path,
        private readonly string $method,
        private readonly string $ip,
        private readonly ?string $userAgent,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function ip(): string
    {
        return $this->ip;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}

