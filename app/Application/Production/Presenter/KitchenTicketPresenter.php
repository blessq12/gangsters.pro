<?php

namespace App\Application\Production\Presenter;

use App\Domain\Core\Production\Entity\KitchenTicket;

final class KitchenTicketPresenter
{
    /**
     * @param  array{
     *     id: int,
     *     number: string|null,
     *     branch_id: int|null,
     *     status: string,
     *     comment: string|null,
     *     lines: list<array{product_id: int, name: string, quantity: int, line_type: string}>
     * }|null  $order
     * @return array<string, mixed>
     */
    public function present(KitchenTicket $ticket, ?array $order = null): array
    {
        $ticketLines = [];
        foreach ($ticket->lines() as $line) {
            $ticketLines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
            ];
        }

        return [
            'id' => $ticket->hasId() ? $ticket->id() : null,
            'order_id' => $ticket->orderId(),
            'order_number' => $order['number'] ?? null,
            'branch_id' => $ticket->branchId(),
            'status' => $ticket->status()->value,
            'lines' => $ticketLines,
            'started_at' => $ticket->startedAt()?->format(DATE_ATOM),
            'completed_at' => $ticket->completedAt()?->format(DATE_ATOM),
            'order' => $order,
        ];
    }
}
