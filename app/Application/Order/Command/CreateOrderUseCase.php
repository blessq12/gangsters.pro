<?php

namespace App\Application\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\OrderBaseUseCase;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Order\Events\OrderCreated;

final class CreateOrderUseCase extends OrderBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(CreateOrderDTO $dto): array
    {
        if (\count($dto->items) === 0) {
            throw new ApiException('Order must contain at least one item.');
        }

        $clientId = $this->authContext->currentClientId();
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new ApiException('Client not found.');
        }
        if (!$client->isActive()) {
            throw new ApiException('Client is blocked or deleted.');
        }

        $customerSnapshot = $this->customerFactory->fromClient($client);

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

        // Адрес в слепке клиента должен совпадать с адресом доставки заказа.
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

        return $this->presenter->present($order);
    }
}
