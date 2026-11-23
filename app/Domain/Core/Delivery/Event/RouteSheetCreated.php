<?php

namespace App\Domain\Core\Delivery\Event;

/**
 * Маршрутный лист выпущен (получил идентичность).
 */
final readonly class RouteSheetCreated
{
    public function __construct(
        public int $routeSheetId,
    ) {}
}
