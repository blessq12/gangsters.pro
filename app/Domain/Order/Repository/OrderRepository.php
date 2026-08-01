<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\ValueObject\OrderId;

interface OrderRepository
{
    public function findById(OrderId $id): ?Order;

    public function findByCheckoutId(string $checkoutId): ?Order;

    public function findByClientRequestId(string $clientRequestId): ?Order;

    public function existsByCheckoutId(string $checkoutId): bool;

    public function existsByClientRequestId(string $clientRequestId): bool;

    /**
     * @return list<Order>
     */
    public function listByClientId(int $clientId): array;

    public function save(Order $order): void;
}
