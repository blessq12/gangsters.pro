<?php

namespace App\Domain\Order\Services;

use App\Domain\Order\Entities\Order;

class OrderDomainService
{
    public function confirm(Order $order): void
    {
        $order->confirm();
    }

    public function pay(Order $order): void
    {
        $order->pay();
    }

    public function cancel(Order $order): void
    {
        $order->cancel();
    }
}

