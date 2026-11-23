<?php

namespace App\Domain\Core\Order\Repository;

interface OrderNoteRepository
{
    /**
     * @return list<array{id: int, body: string, author_employee_id: int|null, created_at: string}>
     */
    public function listByOrderId(int $orderId): array;

    /**
     * @return array{id: int, body: string, author_employee_id: int|null, created_at: string}
     */
    public function add(int $orderId, string $body, ?int $authorEmployeeId): array;
}
