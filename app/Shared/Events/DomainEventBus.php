<?php

namespace App\Shared\Events;

interface DomainEventBus
{
    public function publish(DomainEvent $event): void;

    /**
     * @param iterable<DomainEvent> $events
     */
    public function publishAll(iterable $events): void;
}

