<?php

namespace App\Domain\Order\Enum;

enum OrderStatus: string
{
    case New = 'new';
    case Preorder = 'preorder';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
