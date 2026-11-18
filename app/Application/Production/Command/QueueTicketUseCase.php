<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\QueueTicketDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Entity\KitchenTicket;
use App\Domain\Production\Repository\KitchenTicketRepository;

final class QueueTicketUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly OrderRepository $orders,
        private readonly KitchenTicketPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(QueueTicketDto $dto): array
    {
        $order = $this->orders->findById($dto->orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        if ($order->status() !== OrderStatus::Confirmed) {
            throw new \InvalidArgumentException('В очередь можно поставить только подтверждённый заказ.');
        }

        $existing = $this->tickets->findByOrderId($dto->orderId);
        if ($existing !== null) {
            return $this->presenter->present($existing, $order);
        }

        $ticket = KitchenTicket::queue($dto->orderId);
        $this->tickets->enqueue($ticket);

        return $this->presenter->present($ticket, $order);
    }
}
