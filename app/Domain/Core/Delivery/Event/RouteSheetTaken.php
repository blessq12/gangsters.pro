<?php

namespace App\Domain\Core\Delivery\Event;

final readonly class RouteSheetTaken
{
    /**
     * @param  list<int>  $orderIds
     */
    public function __construct(
        public int $routeSheetId,
        public array $orderIds,
    ) {}
}
