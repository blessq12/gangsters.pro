<?php

namespace App\Domain\Promotion\Enum;

enum ProductPromotionRoleKind: string
{
    case Roll = 'roll';
    case Gift = 'gift';
    case Complement = 'complement';
}
