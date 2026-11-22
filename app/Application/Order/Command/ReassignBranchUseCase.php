<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ReassignBranchDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Переназначение филиала. Старый тикет меняет слушатель BranchReassigned.
 */
final class ReassignBranchUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly BranchRepository $branches,
        private readonly AdminOrderPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ReassignBranchDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->requireOrder($dto->orderId);

            $branch = $this->branches->findById($dto->branchId);
            if ($branch === null) {
                throw new \InvalidArgumentException('Филиал не найден.');
            }

            $order->reassignBranch($branch->id());
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
