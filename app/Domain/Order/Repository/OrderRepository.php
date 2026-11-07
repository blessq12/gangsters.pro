<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

interface OrderRepository
{
    public function findById(int $id): ?Order;

    public function findByClientRequestId(string $clientRequestId): ?Order;

    public function findByFrontpadOrderId(string $frontpadOrderId): ?Order;

    /**
     * @return list<Order>
     */
    public function listByClientId(int $clientId): array;

    public function count(?string $status = null): int;

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array;

    /**
     * @return list<Order>
     */
    public function listPage(int $offset, int $limit, ?string $status = null): array;

    public function save(Order $order): void;
}
