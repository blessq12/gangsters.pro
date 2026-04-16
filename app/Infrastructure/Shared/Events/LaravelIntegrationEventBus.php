<?php

namespace App\Infrastructure\Shared\Events;

use App\Shared\Events\IntegrationEvent;
use App\Shared\Events\IntegrationEventBus;
use Illuminate\Contracts\Events\Dispatcher;

final class LaravelIntegrationEventBus implements IntegrationEventBus
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function publish(IntegrationEvent $event): void
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
