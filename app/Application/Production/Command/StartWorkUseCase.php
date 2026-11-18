<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Entity\KitchenTicket;
use App\Domain\Production\Repository\KitchenTicketRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Старт работы кухни: тикет создаётся (если нет) и стартует здесь.
 * Order в Preparing уходит через KitchenTicketStarted → listener.
 * Всё в одной транзакции: упал listener — откатывается и тикет.
 */
final class StartWorkUseCase
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly OrderRepository $orders,
        private readonly KitchenTicketPresenter $presenter,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(StartWorkDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->orders->findById($dto->orderId);
            if ($order === null) {
                throw new \InvalidArgumentException('Заказ не найден.');
            }

            if ($order->status() !== OrderStatus::Confirmed) {
                throw new \InvalidArgumentException('Начать готовку можно только по подтверждённому заказу.');
            }

            $kitchenId = $order->kitchenId();
            if ($kitchenId === null) {
                throw new \InvalidArgumentException('Заказу не назначена кухня.');
            }

            $ticket = $this->tickets->findByOrderId($dto->orderId);
            if ($ticket === null) {
                $ticket = KitchenTicket::queue($dto->orderId, $kitchenId);
                $this->tickets->enqueue($ticket);
            }

            try {
                $ticket->start($kitchenId);
            } catch (\LogicException $exception) {
                throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
            }

            $this->tickets->save($ticket);

            try {
                foreach ($ticket->pullDomainEvents() as $event) {
                    event($event);
                }
            } catch (\LogicException $exception) {
                throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
            }

            $order = $this->orders->findById($dto->orderId);
            if ($order === null) {
                throw new \InvalidArgumentException('Заказ не найден.');
            }

            return $this->presenter->present($ticket, $order);
        });
    }
}
