<?php

namespace App\Application\Production\Presenter;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Domain\Core\Production\Entity\KitchenTicket;

final class KitchenTicketPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(KitchenTicket $ticket, ?Order $order = null): array
    {
        $ticketLines = [];
        foreach ($ticket->lines() as $line) {
            $ticketLines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
            ];
        }

        $orderNumber = null;
        if ($order !== null && $order->hasId()) {
            $orderNumber = OrderNumber::format($order->id(), $order->createdAt());
        }

        return [
            'id' => $ticket->hasId() ? $ticket->id() : null,
            'order_id' => $ticket->orderId(),
            'order_number' => $orderNumber,
            'branch_id' => $ticket->branchId(),
            'status' => $ticket->status()->value,
            'lines' => $ticketLines,
            'started_at' => $ticket->startedAt()?->format(DATE_ATOM),
            'completed_at' => $ticket->completedAt()?->format(DATE_ATOM),
            'order' => $order !== null ? $this->orderSnapshot($order) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderSnapshot(Order $order): array
    {
        $delivery = $order->delivery();
        $comment = $delivery->address()?->comment();

        $lines = [];
        foreach ($order->lines() as $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'line_type' => $line->lineType()->value,
            ];
        }

        $id = $order->hasId() ? $order->id() : null;

        return [
            'id' => $id,
            'number' => $id !== null ? OrderNumber::format($id, $order->createdAt()) : null,
            'status' => $order->status()->value,
            'lines' => $lines,
            'comment' => $comment,
        ];
    }
}
