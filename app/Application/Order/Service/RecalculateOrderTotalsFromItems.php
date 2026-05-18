<?php

namespace App\Application\Order\Service;

use App\Infrastructure\Order\Model\ORD_Order;

final class RecalculateOrderTotalsFromItems
{
    public function recalculate(ORD_Order $order): ORD_Order
    {
        $order->load('items');

        $subtotal = 0;
        $discountTotal = 0;
        $total = 0;

        foreach ($order->items as $item) {
            $subtotal += (int) $item->row_subtotal;
            $discountTotal += (int) $item->row_discount;
            $total += (int) $item->row_total;
        }

        $order->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total' => $total,
        ]);

        return $order->refresh();
    }
}
