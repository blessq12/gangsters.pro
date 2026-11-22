<?php

namespace App\Application\Production\Query;

use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

final class ListQueueUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly OrderRepository $orders,
        private readonly KitchenTicketPresenter $presenter,
    ) {}

    /**
     * Только чтение: никаких side effects.
     *
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(
        int $branchId,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
    ): array {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        $items = [];
        foreach ($this->tickets->listActiveByBranchPage($branchId, $offset, $limit) as $ticket) {
            $order = $this->orders->findById($ticket->orderId());
            $items[] = $this->presenter->present($ticket, $order);
        }

        return PageParams::wrap(
            $items,
            $this->tickets->countActiveByBranch($branchId),
            $offset,
            $limit,
        );
    }
}
