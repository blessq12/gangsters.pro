<?php

namespace App\Infrastructure\Production\Listener;

use App\Domain\Core\Order\Event\OrderCancelled;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

/**
 * Order публикует отмену; Production отменяет свой тикет, не трогая Order.
 */
final class CancelKitchenTicketOnOrderCancelled
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $ticket = $this->tickets->findByOrderId($event->orderId);
        if ($ticket === null) {
            return;
        }

        try {
            $ticket->cancel();
        } catch (\LogicException) {
            return;
        }

        $this->tickets->save($ticket);

        foreach ($ticket->pullDomainEvents() as $domainEvent) {
            event($domainEvent);
        }
    }
}
