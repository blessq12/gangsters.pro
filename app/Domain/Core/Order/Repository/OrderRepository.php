<?php

namespace App\Domain\Core\Order\Repository;

use App\Domain\Core\Order\Entity\Order;

interface OrderRepository
{
    public function findById(int $id): ?Order;

    public function findByClientRequestId(string $clientRequestId): ?Order;

    /**
     * @return list<Order>
     */
    public function listByClientId(int $clientId): array;

    /**
     * @param  array{
     *     status?: string|null,
     *     q?: string|null,
     *     branch_id?: int|null,
     *     delivery_method?: string|null
     * }  $filters
     */
    public function count(array $filters = []): int;

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array;

    /**
     * @param  array{
     *     status?: string|null,
     *     q?: string|null,
     *     branch_id?: int|null,
     *     delivery_method?: string|null
     * }  $filters
     * @return list<Order>
     */
    public function listPage(int $offset, int $limit, array $filters = []): array;

    public function save(Order $order): void;
}
