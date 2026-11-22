<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Старт уже созданного тикета. Заказ в preparing переводит слушатель KitchenTicketStarted.
 */
final class StartWorkUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly OrderRepository $orders,
        private readonly KitchenTicketPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(StartWorkDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->requireOrder($dto->orderId);
            $ticket = $this->requireTicket($dto->orderId);

            try {
                $ticket->start($order->branchId());
            } catch (\LogicException $exception) {
                throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
            }

            $this->tickets->save($ticket);
            $this->events->publish($ticket);

            return $this->presenter->present($ticket, $this->requireOrder($dto->orderId));
        });
    }

    private function requireOrder(int $orderId): Order
    {
        return $this->orders->findById($orderId)
            ?? throw new \InvalidArgumentException('Заказ не найден.');
    }

    private function requireTicket(int $orderId): KitchenTicket
    {
        return $this->tickets->findByOrderId($orderId)
            ?? throw new \InvalidArgumentException('Тикет не найден. Он создаётся при подтверждении заказа.');
    }
}
