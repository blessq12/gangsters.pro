<?php

namespace App\Domain\Order\Enum;

enum OrderStatus: string
{
    case New = 'new';
    case Preparing = 'preparing';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
}
