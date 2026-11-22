<?php

namespace App\Infrastructure\Production\Listener;

use App\Domain\Core\Order\Event\BranchReassigned;
use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;

/**
 * Филиал переназначен → отменить незавершённый тикет и создать новый на новый филиал.
 * Линии берутся из события, без загрузки Order.
 */
final class ReplaceKitchenTicketOnBranchReassigned
{
    public function __construct(
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
