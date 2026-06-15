<?php

namespace App\Domain\Order\Enum;

enum OrderSource: string
{
    case Site = 'site';
    case Aggregator = 'aggregator';
}
