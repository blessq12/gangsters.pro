<?php

namespace App\Application\Order\Command;

use App\Application\Order\Contracts\CancelOrderContract;
use App\Application\Order\Events\OrderCancelledIntegrationEvent;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEventBus;

final class CancelOrderService implements CancelOrderContract
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly DomainEventBus $domainEvents,
        private readonly IntegrationEventBus $integrationEvents,
    ) {
    }

    public function cancel(Order $order): void
    {
        $this->orders->delete($order->getId());
        $this->domainEvents->publish(new OrderCancelled($order));
        $this->integrationEvents->publish(OrderCancelledIntegrationEvent::fromOrder($order));
    }
}
