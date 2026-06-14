<?php

namespace App\Domain\Order\Enum;

enum OrderClientKind: string
{
    case Guest = 'guest';
    case Registered = 'registered';
}
