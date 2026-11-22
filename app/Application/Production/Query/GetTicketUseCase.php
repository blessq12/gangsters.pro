<?php

namespace App\Application\Production\Query;

use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Core\Production\Port\ProductionOrderSnapshotPort;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

final class GetTicketUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly ProductionOrderSnapshotPort $orders,
        private readonly KitchenTicketPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $orderId): array
    {
        $ticket = $this->tickets->findByOrderId($orderId);
        if ($ticket === null) {
            throw new \InvalidArgumentException('Тикет не найден.');
        }

        return $this->presenter->present($ticket, $this->orders->find($orderId));
    }
}
