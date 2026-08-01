<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

interface OrderRepository
{
    public function findById(int $id): ?Order;

    public function findByClientRequestId(string $clientRequestId): ?Order;

    /**
     * @return list<Order>
     */
    public function listByClientId(int $clientId): array;

    public function save(Order $order): void;
}
