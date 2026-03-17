<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\OrderBaseUseCase;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use LogicException;

final class CreateOrderUseCase extends OrderBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(CreateOrderDTO $dto): array
    {
        if (\count($dto->items) === 0) {
            throw new LogicException('Order must contain at least one item.');
        }

        $client = null;
        $customerSnapshot = null;

        if ($dto->clientId !== null) {
            $client = $this->clients->findById($dto->clientId);
            if ($client === null) {
                throw new LogicException('Client not found.');
            }
            if (!$client->isActive()) {
                throw new LogicException('Client is blocked or deleted.');
            }

            $customerSnapshot = $this->customerFactory->fromClient($client);
        } else {
            $customerSnapshot = $this->customerFactory->forGuest();
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

        $order = $this->orderFactory->create(
            $dto->clientId ?? 0,
            $customerSnapshot,
            $itemsData,
            $deliveryInfo,
            $paymentInfo,
        );

        $this->orders->save($order);

        return $this->presenter->present($order);
    }
}
