<?php

namespace App\Application\Production\Command;

use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Repository\KitchenTicketRepository;

final class CancelTicketUseCase
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
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        if ($order->status() !== OrderStatus::Cancelled) {
            throw new \InvalidArgumentException('Отменить тикет можно только для отменённого заказа.');
        }

        $ticket = $this->tickets->findByOrderId($orderId);
        if ($ticket === null) {
            throw new \InvalidArgumentException('Тикет не найден.');
        }

        try {
            $ticket->cancel();
        } catch (\LogicException $exception) {
            throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
        }

        $this->tickets->save($ticket);

        return $this->presenter->present($ticket, $order);
    }
}
