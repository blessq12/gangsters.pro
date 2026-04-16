<?php

namespace App\Application\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\Contracts\OrderPlacementContract;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\OrderBaseUseCase;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Shared\Auth\ClientAuthContext;
use App\Shared\Events\DomainEventBus;

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
        private readonly OrderPlacementContract $orderPlacement,
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

        $order = $this->orderPlacement->place(
            $clientId,
            $customerSnapshotForOrder,
            $dto->items,
            $deliveryInfo,
            $paymentInfo,
        );

        return $this->presenter->present($order);
    }
}
