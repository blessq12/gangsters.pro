<?php

namespace App\Application\Order\Command;

use App\Application\Order\Contracts\OrderPlacementContract;
use App\Application\Order\Events\OrderCreatedIntegrationEvent;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEventBus;

final class PlaceOrderService implements OrderPlacementContract
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderFactory $orderFactory,
        private readonly OrderItemsFactory $itemsFactory,
        private readonly DomainEventBus $domainEvents,
        private readonly IntegrationEventBus $integrationEvents,
    ) {
    }

    public function place(
        ?int $clientId,
        CustomerSnapshot $customerSnapshot,
        array $items,
        DeliveryInfo $deliveryInfo,
        PaymentInfo $paymentInfo,
        int $deliveryFeeKopecks = 0,
        ?array $deliveryPricingSnapshot = null,
    ): Order {
        $order = $this->orderFactory->create(
            clientId: $clientId,
            customer: new CustomerSnapshot(
                name: $customerSnapshot->name,
                phone: $customerSnapshot->phone,
                email: $customerSnapshot->email,
                address: $deliveryInfo->address,
            ),
            itemsData: $this->itemsFactory->buildItemsData($items),
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            deliveryFeeKopecks: $deliveryFeeKopecks,
            deliveryPricingSnapshot: $deliveryPricingSnapshot,
        );

        $this->orders->save($order);
        $this->domainEvents->publish(new OrderCreated($order));
        $this->integrationEvents->publish(OrderCreatedIntegrationEvent::fromOrder($order));

        return $order;
    }
}
