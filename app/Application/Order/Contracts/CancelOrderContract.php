<?php

namespace App\Application\Order\Contracts;

use App\Domain\Order\Entities\Order;

interface CancelOrderContract
{
    public function cancel(Order $order): void;
}
