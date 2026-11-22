<?php

namespace App\Infrastructure\Production\Listener;

use App\Domain\Core\Order\Event\BranchReassigned;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;

/**
 * Филиал переназначен → отменить незавершённый тикет и создать новый на новый филиал.
 * Новый тикет ставится сразу (не ждём повторный OrderConfirmed).
 */
final class ReplaceKitchenTicketOnBranchReassigned
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function handle(BranchReassigned $event): void
    {
        $existing = $this->tickets->findByOrderId($event->orderId);
        if ($existing !== null) {
            if ($existing->status() === KitchenTicketStatus::Completed) {
                return;
            }

            if ($existing->status() !== KitchenTicketStatus::Cancelled) {
                try {
                    $existing->cancel();
                } catch (\LogicException) {
                    return;
                }
                $this->tickets->save($existing);
                foreach ($existing->pullDomainEvents() as $domainEvent) {
                    event($domainEvent);
                }
            }

            $this->tickets->delete($existing);
        }

        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $lines = [];
        foreach ($order->productionLines() as $line) {
            $lines[] = new KitchenTicketLine(
                productId: $line->productId(),
                name: $line->name(),
                quantity: $line->quantity(),
            );
        }

        if ($lines === []) {
            return;
        }

        $ticket = KitchenTicket::queue(
            orderId: $event->orderId,
            branchId: $event->branchId,
            lines: $lines,
        );
        $this->tickets->enqueue($ticket);
    }
}
