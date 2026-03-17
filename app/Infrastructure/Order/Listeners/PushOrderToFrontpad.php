<?php

namespace App\Infrastructure\Order\Listeners;

use App\Events\OrderCreatedEvent;
use App\Domain\Order\Integrations\FrontpadOrderGateway;

final class PushOrderToFrontpad
{
    public function __construct(
        private readonly FrontpadOrderGateway $gateway,
    ) {
    }

    public function handle(OrderCreatedEvent $event): void
    {
        $this->gateway->pushOrder($event->order);
    }
}

