<?php

namespace Tests\Application\Core\Support;

use App\Application\Shared\DomainEventPublisher;

final class RecordingDomainEventPublisher implements DomainEventPublisher
{
    /** @var list<object> */
    public array $events = [];

    public function __construct(
        private readonly \Closure $onEvent,
    ) {}

    public function publish(object $aggregate): void
    {
        if (! method_exists($aggregate, 'pullDomainEvents')) {
            throw new \LogicException('Агрегат не публикует доменные события.');
        }

        foreach ($aggregate->pullDomainEvents() as $event) {
            $this->events[] = $event;
            ($this->onEvent)($event);
        }
    }
}
