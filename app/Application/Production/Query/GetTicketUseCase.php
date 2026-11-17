<?php

namespace App\Application\Production\Query;

use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Repository\KitchenTicketRepository;

final class GetTicketUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly OrderRepository $orders,
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

        $order = $this->orders->findById($orderId);

        return $this->presenter->present($ticket, $order);
    }
}
