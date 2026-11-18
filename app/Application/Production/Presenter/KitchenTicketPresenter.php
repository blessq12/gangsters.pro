<?php

namespace App\Application\Production\Presenter;

use App\Domain\Order\Entity\Order;
use App\Domain\Production\Entity\KitchenTicket;

final class KitchenTicketPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(KitchenTicket $ticket, ?Order $order = null): array
    {
        return [
            'id' => $ticket->hasId() ? $ticket->id() : null,
            'order_id' => $ticket->orderId(),
            'kitchen_id' => $ticket->kitchenId(),
            'status' => $ticket->status()->value,
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
        $comment = null;
        if (is_array($delivery['address'] ?? null)) {
            $comment = $delivery['address']['comment'] ?? null;
        }
        if ($comment === null || $comment === '') {
            $comment = $delivery['comment'] ?? null;
        }

        return [
            'id' => $order->hasId() ? $order->id() : null,
            'status' => $order->status()->value,
            'lines' => is_array($order->cart()['lines'] ?? null) ? $order->cart()['lines'] : [],
            'comment' => $comment,
        ];
    }
}
