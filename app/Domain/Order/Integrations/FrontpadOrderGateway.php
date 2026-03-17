<?php

namespace App\Domain\Order\Integrations;

use App\Domain\Order\Entities\Order;

interface FrontpadOrderGateway
{
    public function pushOrder(Order $order): void;
}

