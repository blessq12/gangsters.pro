<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ReassignKitchenDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Supporting\Company\Repository\KitchenRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Переназначение кухни. Старый тикет меняет слушатель KitchenReassigned.
 */
final class ReassignKitchenUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly KitchenRepository $kitchens,
        private readonly AdminOrderPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ReassignKitchenDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->requireOrder($dto->orderId);

            $kitchen = $this->kitchens->findById($dto->kitchenId);
            if ($kitchen === null) {
                throw new \InvalidArgumentException('Кухня не найдена.');
            }

            $order->reassignKitchen($kitchen->id(), $kitchen->branchId());
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
