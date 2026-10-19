<?php

namespace App\Domain\Order\Enum;

enum OrderStatus: string
{
    case New = 'new';
    case Preorder = 'preorder';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case InTransit = 'in_transit';
    case Cancelled = 'cancelled';
    case Delivered = 'delivered';
}
