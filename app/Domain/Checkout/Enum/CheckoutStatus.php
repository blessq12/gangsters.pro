<?php

namespace App\Domain\Checkout\Enum;

enum CheckoutStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
}
