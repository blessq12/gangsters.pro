<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\QueueTicketDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

/**
 * Тикет создаётся только по OrderConfirmed.
 * Здесь — получить уже существующий тикет.
 */
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

        $ticket = $this->tickets->findByOrderId($dto->orderId);
        if ($ticket === null) {
            throw new \InvalidArgumentException(
                'Тикет ещё не создан. Он появляется после подтверждения заказа.',
            );
        }

        return $this->presenter->present($ticket, $order);
    }
}
