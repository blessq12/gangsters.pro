<?php

namespace App\Domain\Promotion\Enum;

enum PromotionOrderChannel: string
{
    case Pickup = 'pickup';
    case Courier = 'courier';
}
