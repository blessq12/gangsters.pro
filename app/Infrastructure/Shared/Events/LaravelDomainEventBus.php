<?php

namespace App\Infrastructure\Shared\Events;

use App\Shared\Events\DomainEvent;
use App\Shared\Events\DomainEventBus;
use Illuminate\Contracts\Events\Dispatcher;

final class LaravelDomainEventBus implements DomainEventBus
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function publish(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function publishAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}

