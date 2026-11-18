<?php

namespace App\Application\Order\Command;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Order\Repository\OrderRepository;

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

        $order->completePickup();
        $this->orders->save($order);

        return $this->presenter->present($order);
    }
}
