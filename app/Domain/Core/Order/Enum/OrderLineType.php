<?php

namespace App\Domain\Core\Order\Enum;

enum OrderLineType: string
{
    case Regular = 'regular';
    case Gift = 'gift';
    case Complement = 'complement';
    case Set = 'set';
}
