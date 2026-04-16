<?php

namespace App\Infrastructure\Order\Listeners;

use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Integrations\FrontpadOrderGateway;

final class PushOrderToFrontpad
{
    public function __construct(
        private readonly FrontpadOrderGateway $gateway,
    ) {
    }

    public function handle(OrderCreated $event): void
    {
        $this->gateway->pushOrder($event->order());
    }
}

