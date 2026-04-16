<?php

namespace App\Shared\Events;

interface IntegrationEventBus
{
    public function publish(IntegrationEvent $event): void;

    /**
     * @param iterable<IntegrationEvent> $events
     */
    public function publishAll(iterable $events): void;
}
