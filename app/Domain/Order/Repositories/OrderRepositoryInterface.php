<?php

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Entities\Order;

interface OrderRepositoryInterface
{
    public function getById(string $id): Order;

    public function save(Order $order): void;
}

