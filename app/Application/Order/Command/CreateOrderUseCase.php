<?php

namespace App\Application\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\Events\OrderCreatedIntegrationEvent;
use App\Application\Order\OrderBaseUseCase;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Order\Events\OrderCreated;
use App\Shared\Auth\ClientAuthContext;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEventBus;

final class CreateOrderUseCase extends OrderBaseUseCase
{
    public function __construct(
        OrderRepositoryInterface $orders,
        OrderFactory $orderFactory,
        CustomerSnapshotProvider $customerSnapshots,
        ClientAuthContext $authContext,
        OrderItemsFactory $itemsFactory,
        OrderPresenter $presenter,
        DomainEventBus $events,
        private readonly IntegrationEventBus $integrationEvents,
    ) {
        parent::__construct(
            $orders,
            $orderFactory,
            $customerSnapshots,
            $authContext,
            $itemsFactory,
            $presenter,
            $events,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateOrderDTO $dto, ?int $authenticatedClientId): array
    {
        if (\count($dto->items) === 0) {
            throw new ApiException('Order must contain at least one item.');
        }

        if ($authenticatedClientId !== null) {
            $customerSnapshot = $this->customerSnapshots->forAuthenticatedClient($authenticatedClientId);
            $clientId = $authenticatedClientId;
        } else {
            $name = $dto->guestCustomerName ?? '';
            $phone = $dto->guestCustomerPhone ?? '';
            if (trim($name) === '' || trim($phone) === '') {
                throw new ApiException('Guest order requires customer name and phone.');
            }
            $customerSnapshot = $this->customerSnapshots->forGuestContact(
                $name,
                $phone,
                $dto->guestCustomerEmail,
            );
            $clientId = null;
        }

        $itemsData = $this->itemsFactory->buildItemsData($dto->items);

        $deliveryInfo = new DeliveryInfo(
            method: $dto->deliveryMethod,
            address: $dto->deliveryAddress,
            comment: $dto->deliveryComment,
        );

        $paymentInfo = new PaymentInfo(
            method: $dto->paymentMethod,
            status: PaymentStatus::Unpaid->value,
        );

        $customerSnapshotForOrder = new CustomerSnapshot(
            name: $customerSnapshot->name,
            phone: $customerSnapshot->phone,
            email: $customerSnapshot->email,
            address: $deliveryInfo->address,
        );

        $order = $this->orderFactory->create(
            $clientId,
            $customerSnapshotForOrder,
            $itemsData,
            $deliveryInfo,
            $paymentInfo,
        );

        $this->orders->save($order);
        $this->events->publish(new OrderCreated($order));
        $this->integrationEvents->publish(OrderCreatedIntegrationEvent::fromOrder($order));

        return $this->presenter->present($order);
    }
}
