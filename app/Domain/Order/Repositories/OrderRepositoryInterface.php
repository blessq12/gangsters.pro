<?php

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Entities\Order;

interface OrderRepositoryInterface
{
    public function getById(string $id): Order;

    /**
     * @return Order[]
     */
    public function findByClientId(int $clientId): array;

    public function save(Order $order): void;

    public function delete(string $id): void;
}

