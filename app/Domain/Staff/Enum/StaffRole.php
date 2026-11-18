<?php

namespace App\Domain\Staff\Enum;

enum StaffRole: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case Kitchen = 'kitchen';
    case Courier = 'courier';
}
