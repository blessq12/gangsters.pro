<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\ValueObject\OrderId;

interface OrderRepository
{
    public function findById(OrderId $id): ?Order;

    public function findByCheckoutId(string $checkoutId): ?Order;

    public function existsByCheckoutId(string $checkoutId): bool;

    public function save(Order $order): void;
}
