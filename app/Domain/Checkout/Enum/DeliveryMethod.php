<?php

namespace App\Domain\Checkout\Enum;

enum DeliveryMethod: string
{
    case Courier = 'courier';
    case Pickup = 'pickup';
}
