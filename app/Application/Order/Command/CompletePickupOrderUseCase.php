<?php

namespace App\Application\Order\Command;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Выдача самовывоза. Оплату закрывает слушатель OrderPickedUp.
 */
final class CompletePickupOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $orderId): array
    {
        return $this->transaction->run(function () use ($orderId): array {
            $order = $this->requireOrder($orderId);
            $order->markDeliveredPickup();
            $this->orders->save($order);
            $this->events->publish($order);

            return $this->presenter->present($this->requireOrder($order->id()));
        });
    }

    private function requireOrder(int $orderId): Order
    {
        return $this->orders->findById($orderId)
            ?? throw new \InvalidArgumentException('Заказ не найден.');
    }
}
