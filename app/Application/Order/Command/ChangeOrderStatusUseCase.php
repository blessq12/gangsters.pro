<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ChangeOrderStatusDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;

final class ChangeOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ChangeOrderStatusDto $dto): array
    {
        $order = $this->orders->findById($dto->orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        $target = OrderStatus::tryFrom(trim($dto->targetStatus));
        if ($target === null) {
            throw new \InvalidArgumentException('Некорректный целевой статус.');
        }

        match ($target) {
            OrderStatus::Confirmed => $order->accept(),
            OrderStatus::Preparing => $order->startPreparing(),
            OrderStatus::Ready => $order->markReady(),
            OrderStatus::InTransit => $order->markInTransit(),
            OrderStatus::Delivered => $order->markDelivered(),
            OrderStatus::Completed => $order->completePickup(),
            OrderStatus::Cancelled => $order->cancel(),
            OrderStatus::New, OrderStatus::Preorder => throw new \InvalidArgumentException(
                'Нельзя перевести заказ в статус «'.$target->value.'» через change status.',
            ),
        };

        $this->orders->save($order);

        return $this->presenter->present($order);
    }
}
