<?php

namespace App\Domain\Delivery\Event;

final readonly class OrderAssignedToRoute
{
    public function __construct(
        public int $orderId,
        public int $routeSheetId,
    ) {}
}
