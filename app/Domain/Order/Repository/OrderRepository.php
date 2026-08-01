<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

interface OrderRepository
{
    public function findByClientRequestId(string $clientRequestId): ?Order;

    public function save(Order $order): void;
}
