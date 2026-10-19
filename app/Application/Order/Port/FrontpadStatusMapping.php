<?php

namespace App\Application\Order\Port;

use App\Domain\Order\Enum\OrderStatus;

interface FrontpadStatusMapping
{
    public function toDomain(int $frontpadStatus): ?OrderStatus;
}
