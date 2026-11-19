<?php

namespace App\Domain\Core\Order\Enum;

enum ProductPromotionRoleKind: string
{
    case Roll = 'roll';
    case Gift = 'gift';
    case Complement = 'complement';
}
