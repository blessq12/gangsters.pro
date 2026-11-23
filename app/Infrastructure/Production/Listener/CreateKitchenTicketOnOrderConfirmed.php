<?php

namespace App\Infrastructure\Production\Listener;

use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;

/**
 * Order подтверждён → Production создаёт KitchenTicket со снимком позиций из события.
 */
final class CreateKitchenTicketOnOrderConfirmed
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function handle(OrderConfirmed $event): void
    {
        $existing = $this->tickets->findByOrderId($event->orderId);
        if ($existing !== null) {
            // Активный или отменённый тикет на order_id уже есть (unique) — не дублируем.
            return;
        }

        $lines = $this->mapLines($event->productionLines);
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

    /**
     * @param  list<array{product_id: int, name: string, quantity: int}>  $snapshots
     * @return list<KitchenTicketLine>
     */
    private function mapLines(array $snapshots): array
    {
        $lines = [];
        foreach ($snapshots as $snapshot) {
            $lines[] = new KitchenTicketLine(
                productId: (int) $snapshot['product_id'],
                name: (string) $snapshot['name'],
                quantity: (int) $snapshot['quantity'],
            );
        }

        return $lines;
    }
}
