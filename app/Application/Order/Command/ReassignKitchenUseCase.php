<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ReassignKitchenDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Company\Repository\KitchenRepository;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Enum\KitchenTicketStatus;
use App\Domain\Production\Repository\KitchenTicketRepository;

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

        $order->assignKitchen($kitchen->id(), $kitchen->branchId());
        $this->orders->save($order);

        return $this->presenter->present($order);
    }
}
