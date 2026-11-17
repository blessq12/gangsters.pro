<?php

namespace App\Application\Order\Presenter;

use App\Domain\Order\Entity\Order;

final class OrderPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $cart = $order->cart();
        $totals = is_array($cart['totals'] ?? null)
            ? $cart['totals']
            : [
                'items_total' => 0,
                'discount' => 0,
                'delivery_fee' => 0,
                'grand_total' => $order->totalRubles() * 100,
                'grand_total_rubles' => $order->totalRubles(),
            ];

        return [
            'id' => $order->hasId() ? $order->id() : null,
            'status' => $order->status()->value,
            'cart' => $cart,
            'client' => $order->client(),
            'delivery' => $order->delivery(),
            'payment' => $order->payment(),
            'totals' => $totals,
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'client_request_id' => $order->clientRequestId(),
        ];
    }
}
