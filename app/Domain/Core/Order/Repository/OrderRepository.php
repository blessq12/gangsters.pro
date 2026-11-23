<?php

namespace App\Domain\Core\Order\Repository;

use App\Domain\Core\Order\Entity\Order;
use DateTimeImmutable;

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
     * Счётчики заказов за период (по created_at), опционально по филиалу.
     *
     * @return array<string, int>
     */
    public function countByStatusFiltered(
        ?int $branchId = null,
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
    ): array;

    /**
     * Снимок операционных статусов «сейчас».
     *
     * @return array{
     *     new: int,
     *     preparing: int,
     *     ready: int,
     *     ready_courier: int,
     *     in_transit: int
     * }
     */
    public function countOpsSnapshot(?int $branchId = null): array;

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
