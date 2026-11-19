<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ReassignKitchenDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Supporting\Company\Repository\KitchenRepository;

/**
 * Переназначение кухни → KitchenReassigned.
 * Production-listener отменяет незавершённый тикет и создаёт новый на новую кухню.
 */
final class ReassignKitchenUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly KitchenRepository $kitchens,
        private readonly KitchenTicketRepository $tickets,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ReassignKitchenDto $dto): array
    {
        $order = $this->orders->findById($dto->orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        $ticket = $this->tickets->findByOrderId($dto->orderId);
        if ($ticket !== null) {
            $status = $ticket->status();
            if (
                $status === KitchenTicketStatus::Started
                || $status === KitchenTicketStatus::Completed
            ) {
                throw new \InvalidArgumentException(
                    'Нельзя переназначить кухню: тикет уже в работе или завершён.',
                );
            }
        }

        $kitchen = $this->kitchens->findById($dto->kitchenId);
        if ($kitchen === null) {
            throw new \InvalidArgumentException('Кухня не найдена.');
        }

        $order->reassignKitchen($kitchen->id(), $kitchen->branchId());
        $this->orders->save($order);

        foreach ($order->pullDomainEvents() as $event) {
            event($event);
        }

        $saved = $this->orders->findById($order->id());
        if ($saved === null) {
            throw new \RuntimeException('Заказ не найден после переназначения.');
        }

        return $this->presenter->present($saved);
    }
}
