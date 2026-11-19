<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\CompleteWorkDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

/**
 * Завершение работы кухни. Order → Ready через KitchenTicketCompleted → listener.
 */
final class CompleteWorkUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly OrderRepository $orders,
        private readonly KitchenTicketPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CompleteWorkDto $dto): array
    {
        $ticket = $this->tickets->findByOrderId($dto->orderId);
        if ($ticket === null) {
            throw new \InvalidArgumentException('Тикет не найден.');
        }

        try {
            $ticket->complete();
        } catch (\LogicException $exception) {
            throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
        }

        $this->tickets->save($ticket);

        foreach ($ticket->pullDomainEvents() as $event) {
            event($event);
        }

        $order = $this->orders->findById($dto->orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        return $this->presenter->present($ticket, $order);
    }
}
