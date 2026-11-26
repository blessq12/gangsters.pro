<?php

namespace App\Domain\Core\Order\Repository;

interface OrderNoteRepository
{
    /**
     * @return list<array{
     *     id: int,
     *     body: string,
     *     author_employee_id: int|null,
     *     author_name: string|null,
     *     author_role: string|null,
     *     created_at: string
     * }>
     */
    public function listByOrderId(int $orderId): array;

    public function countByOrderId(int $orderId): int;

    /**
     * @param  list<int>  $orderIds
     * @return array<int, int> orderId → count
     */
    public function countsByOrderIds(array $orderIds): array;

    /**
     * @return array{
     *     id: int,
     *     body: string,
     *     author_employee_id: int|null,
     *     author_name: string|null,
     *     author_role: string|null,
     *     created_at: string
     * }
     */
    public function add(int $orderId, string $body, ?int $authorEmployeeId): array;
}
