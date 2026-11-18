<?php

namespace App\Application\Production\Query;

use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Repository\KitchenTicketRepository;

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
     * @return list<array<string, mixed>>
     */
    public function execute(int $kitchenId): array
    {
        $result = [];

        foreach ($this->tickets->listActiveByKitchen($kitchenId) as $ticket) {
            $order = $this->orders->findById($ticket->orderId());
            $result[] = $this->presenter->present($ticket, $order);
        }

        return $result;
    }
}
