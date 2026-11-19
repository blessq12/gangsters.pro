<?php

namespace App\Application\Shared;

/**
 * Единственная дверь Application в событийный контур.
 * Команда не рассылает факты сама и не подменяет реакцию слушателей.
 */
interface DomainEventPublisher
{
    public function publish(object $aggregate): void;
}
