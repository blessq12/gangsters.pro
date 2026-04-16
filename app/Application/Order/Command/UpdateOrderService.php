<?php

namespace App\Application\Order\Command;

use App\Application\Order\Contracts\UpdateOrderContract;
use App\Application\Order\Events\OrderUpdatedIntegrationEvent;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Events\OrderUpdated;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEventBus;

final class UpdateOrderService implements UpdateOrderContract
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderFactory $orderFactory,
        private readonly OrderItemsFactory $itemsFactory,
        private readonly DomainEventBus $domainEvents,
        private readonly IntegrationEventBus $integrationEvents,
    ) {
    }

    public function update(
        Order $existing,
        ?int $clientId,
        CustomerSnapshot $customerSnapshot,
        array $items,
        ?DeliveryInfo $deliveryInfo,
        ?PaymentInfo $paymentInfo,
    ): Order {
        $order = $this->orderFactory->rebuildOrder(
            id: $existing->getId(),
            clientId: $clientId,
            customer: new CustomerSnapshot(
                name: $customerSnapshot->name,
                phone: $customerSnapshot->phone,
                email: $customerSnapshot->email,
                address: $deliveryInfo?->address,
            ),
            status: $existing->getStatus(),
            itemsData: $this->itemsFactory->buildItemsData($items),
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            createdAt: $existing->getCreatedAt(),
        );

        $this->orders->save($order);
        $this->domainEvents->publish(new OrderUpdated($order));
        $this->integrationEvents->publish(OrderUpdatedIntegrationEvent::fromOrder($order));

        return $order;
    }
}
