<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\MarkTicketLineDoneDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Port\ProductionOrderSnapshotPort;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Повар отмечает позицию тикета готовой (свободный пул).
 */
final class MarkTicketLineDoneUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly ProductionOrderSnapshotPort $orders,
        private readonly KitchenTicketPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(MarkTicketLineDoneDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->requireOrder($dto->orderId);
            $ticket = $this->requireTicket($dto->orderId);

            try {
                $ticket->markLineDone(
                    lineId: $dto->lineId,
                    employeeId: $dto->employeeId,
                    orderBranchId: $order['branch_id'] ?? null,
                );
            } catch (\LogicException|\InvalidArgumentException $exception) {
                throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
            }

            $this->tickets->save($ticket);
            $this->events->publish($ticket);

            return $this->presenter->present($ticket, $this->requireOrder($dto->orderId));
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function requireOrder(int $orderId): array
    {
        return $this->orders->find($orderId)
            ?? throw new \InvalidArgumentException('Заказ не найден.');
    }

    private function requireTicket(int $orderId): KitchenTicket
    {
        return $this->tickets->findByOrderId($orderId)
            ?? throw new \InvalidArgumentException('Тикет не найден.');
    }
}
