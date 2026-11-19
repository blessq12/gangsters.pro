<?php

namespace App\Application\Production\Command;

use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Старт работы кухни по уже существующему тикету (создан по OrderConfirmed).
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
                throw new \InvalidArgumentException(
                    'Тикет не найден. Он создаётся при подтверждении заказа.',
                );
            }

            try {
                $ticket->start($kitchenId);
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
        });
    }
}
