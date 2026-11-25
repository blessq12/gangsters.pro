<?php

namespace App\Domain\Core\Order\Repository;

interface OrderAuditRepository
{
    /**
     * @param  list<array{field: string, old: mixed, new: mixed}>  $changes
     * @return array{id: int, order_id: int, author_employee_id: int|null, changes: list<array{field: string, old: mixed, new: mixed}>, created_at: string}
     */
    public function add(int $orderId, ?int $authorEmployeeId, array $changes): array;

    /**
     * @return list<array{id: int, order_id: int, author_employee_id: int|null, changes: list<array{field: string, old: mixed, new: mixed}>, created_at: string}>
     */
    public function listByOrderId(int $orderId): array;
}
