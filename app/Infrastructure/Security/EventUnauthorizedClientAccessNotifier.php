<?php

namespace App\Infrastructure\Security;

use App\Application\Security\UnauthorizedClientAccessNotifier;
use App\Domain\Client\Events\ClientUnauthorizedAccessDetected;
use App\Shared\Events\DomainEventBus;

final class EventUnauthorizedClientAccessNotifier implements UnauthorizedClientAccessNotifier
{
    public function __construct(
        private readonly DomainEventBus $events,
    ) {
    }

    public function notify(string $path, string $method, string $ip, ?string $userAgent): void
    {
        $this->events->publish(new ClientUnauthorizedAccessDetected(
            path: $path,
            method: $method,
            ip: $ip,
            userAgent: $userAgent,
        ));
    }
}

