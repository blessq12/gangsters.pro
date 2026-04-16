<?php

namespace App\Application\Order\Events;

use App\Domain\Order\Entities\Order;
use App\Shared\Events\IntegrationEvent;

final class OrderCancelledIntegrationEvent implements IntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly ?int $clientId,
        public readonly string $status,
        public readonly ?string $updatedAt,
    ) {
    }

    public static function fromOrder(Order $order): self
    {
        return new self(
            orderId: $order->getId(),
            clientId: $order->getClientId(),
            status: $order->getStatus()->value,
            updatedAt: $order->getUpdatedAt()->format(DATE_ATOM),
        );
    }
}
