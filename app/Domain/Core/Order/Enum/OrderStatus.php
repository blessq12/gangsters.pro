<?php

namespace App\Domain\Core\Order\Enum;

enum OrderStatus: string
{
    case New = 'new';
    case Preorder = 'preorder';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Packed = 'packed';
    case AssignedToRoute = 'assigned_to_route';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case WrittenOff = 'written_off';
    case Cancelled = 'cancelled';
}
