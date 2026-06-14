<?php

namespace App\Domain\Checkout\Enum;

enum ClientKind: string
{
    case Guest = 'guest';
    case Registered = 'registered';
}
