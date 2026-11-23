<?php

namespace App\Infrastructure\Shared\Event;

use App\Application\Shared\DomainEventPublisher;

/**
 * Отдаёт снятые с агрегата факты диспетчеру, на который подписаны слушатели.
 */
final class LaravelDomainEventPublisher implements DomainEventPublisher
{
    public function publish(object $aggregate): void
    {
        if (! method_exists($aggregate, 'pullDomainEvents')) {
            throw new \LogicException('Агрегат не публикует доменные события.');
        }

        foreach ($aggregate->pullDomainEvents() as $event) {
            event($event);
        }
    }
}
