<?php

namespace App\Application\Operations\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\DTO\ChangeOrderStatusDTO;
use App\Application\Operations\Order\Presenter\AdminOrderPresenter;
use App\Application\Order\Events\OrderUpdatedIntegrationEvent;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Shared\Events\IntegrationEventBus;

final class ChangeOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly AdminOrderPresenter $presenter,
        private readonly IntegrationEventBus $integrationEvents,
    ) {
    }

    public function execute(ChangeOrderStatusDTO $dto): array
    {
        $order = $this->orders->getById($dto->orderId);

        match ($dto->status) {
            'preparing' => $order->markPreparing(),
            'in_transit' => $order->markInTransit(),
            'delivered' => $order->markDelivered(),
            default => throw new ApiException('Unsupported order status.', 422),
        };

        $this->orders->save($order);
        $this->integrationEvents->publish(OrderUpdatedIntegrationEvent::fromOrder($order));

        return $this->presenter->presentDetail($order);
    }
}
