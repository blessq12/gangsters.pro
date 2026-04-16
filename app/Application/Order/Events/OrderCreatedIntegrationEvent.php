<?php

namespace App\Application\Order\Events;

use App\Domain\Order\Entities\Order;
use App\Shared\Events\IntegrationEvent;

final class OrderCreatedIntegrationEvent implements IntegrationEvent
{
    /**
     * @param  array<int, array{id: string, product_id: int|null, quantity: int, final_price: int}>  $items
     */
    public function __construct(
        public readonly string $orderId,
        public readonly ?int $clientId,
        public readonly string $status,
        public readonly string $paymentStatus,
        public readonly int $total,
        public readonly array $items,
        public readonly ?string $createdAt,
    ) {
    }

    public static function fromOrder(Order $order): self
    {
        return new self(
            orderId: $order->getId(),
            clientId: $order->getClientId(),
            status: $order->getStatus()->value,
            paymentStatus: $order->getPaymentInfo()?->status ?? '',
            total: $order->getTotal(),
            items: array_map(
                static fn ($item): array => [
                    'id' => $item->getId(),
                    'product_id' => $item->getProductOriginalId(),
                    'quantity' => $item->getQuantity(),
                    'final_price' => $item->getRowTotal(),
                ],
                $order->getItems(),
            ),
            createdAt: $order->getCreatedAt()?->format(DATE_ATOM),
        );
    }
}
