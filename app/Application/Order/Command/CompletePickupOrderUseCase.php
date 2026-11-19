<?php

namespace App\Application\Order\Command;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Core\Order\Repository\OrderRepository;

final class CompletePickupOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $orderId): array
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        $order->markDeliveredPickup();
        $this->orders->save($order);

        foreach ($order->pullDomainEvents() as $event) {
            event($event);
        }

        $saved = $this->orders->findById($orderId);
        if ($saved === null) {
            throw new \RuntimeException('Заказ не найден после выдачи.');
        }

        return $this->presenter->present($saved);
    }
}
