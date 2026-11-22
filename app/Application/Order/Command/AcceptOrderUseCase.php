<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Подтверждение: accept + confirm.
 * Филиал уже на заказе с create; тикет/платёж — слушатели OrderConfirmed.
 */
final class AcceptOrderUseCase
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
    public function execute(AcceptOrderDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->requireOrder($dto->orderId);

            if ($order->branchId() === null || $order->branchId() < 1) {
                throw new \InvalidArgumentException('У заказа нет филиала — нельзя подтвердить.');
            }

            $order->accept();
            $order->confirm();
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
