<?php

namespace App\Shared\Enum;

enum ClientKind: string
{
    case Guest = 'guest';
    case Registered = 'registered';
}
