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
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(
        int $branchId,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
    ): array {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        $page = $this->tickets->listActiveByBranchPage($branchId, $offset, $limit);
        $orderIds = [];
        foreach ($page as $ticket) {
            $orderIds[] = $ticket->orderId();
        }
        $snapshots = $this->orders->findMany($orderIds);

        $items = [];
        foreach ($page as $ticket) {
            $items[] = $this->presenter->present($ticket, $snapshots[$ticket->orderId()] ?? null);
        }

        return PageParams::wrap(
            $items,
            $this->tickets->countActiveByBranch($branchId),
            $offset,
            $limit,
        );
    }
}
