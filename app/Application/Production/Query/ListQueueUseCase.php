<?php

namespace App\Application\Production\Query;

use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Production\Port\ProductionOrderSnapshotPort;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

final class ListQueueUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly ProductionOrderSnapshotPort $orders,
        private readonly KitchenTicketPresenter $presenter,
    ) {}

    /**
     * @return array{
     *   pool: array{items: list<array<string, mixed>>, total: int, offset: int, limit: int},
     *   mine: list<array<string, mixed>>
     * }
     */
    public function execute(
        int $branchId,
        int $employeeId,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
    ): array {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        $poolTickets = $this->tickets->listQueuedByBranchPage($branchId, $offset, $limit);
        $mineTickets = $employeeId > 0
            ? $this->tickets->listMineStartedByBranch($branchId, $employeeId)
            : [];

        $orderIds = [];
        foreach ($poolTickets as $ticket) {
            $orderIds[] = $ticket->orderId();
        }
        foreach ($mineTickets as $ticket) {
            $orderIds[] = $ticket->orderId();
        }
        $snapshots = $this->orders->findMany(array_values(array_unique($orderIds)));

        $poolItems = [];
        foreach ($poolTickets as $ticket) {
            $poolItems[] = $this->presenter->present($ticket, $snapshots[$ticket->orderId()] ?? null);
        }

        $mineItems = [];
        foreach ($mineTickets as $ticket) {
            $mineItems[] = $this->presenter->present($ticket, $snapshots[$ticket->orderId()] ?? null);
        }

        return [
            'pool' => PageParams::wrap(
                $poolItems,
                $this->tickets->countQueuedByBranch($branchId),
                $offset,
                $limit,
            ),
            'mine' => $mineItems,
        ];
    }
}
