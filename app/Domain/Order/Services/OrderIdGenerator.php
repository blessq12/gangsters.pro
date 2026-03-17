<?php

namespace App\Domain\Order\Services;

interface OrderIdGenerator
{
    public function generate(): string;
}

