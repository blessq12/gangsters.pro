<?php

namespace App\Infrastructure\Order\Service;

use App\Domain\Order\Services\OrderIdGenerator;

final class RandomOrderIdGenerator implements OrderIdGenerator
{
    public function generate(): string
    {
        return 'ORD-' . random_int(100000, 999999);
    }
}

