<?php

namespace App\Domain\Order\Enum;

enum OrderDeliveryMethod: string
{
    case Courier = 'courier';
    case Pickup = 'pickup';
}
