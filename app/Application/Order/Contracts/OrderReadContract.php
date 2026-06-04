<?php

namespace App\Application\Order\Contracts;

interface OrderReadContract
{
    /**
     * @return array<string, mixed>|null
     */
    public function findPresentedById(string $orderId): ?array;
}
