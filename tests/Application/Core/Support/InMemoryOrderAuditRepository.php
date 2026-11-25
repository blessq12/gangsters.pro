<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Order\Repository\OrderAuditRepository;
use DateTimeImmutable;

final class InMemoryOrderAuditRepository implements OrderAuditRepository
{
    /** @var list<array{id: int, order_id: int, author_employee_id: int|null, changes: list<array{field: string, old: mixed, new: mixed}>, created_at: string}> */
    private array $rows = [];

    private int $nextId = 1;

    public function add(int $orderId, ?int $authorEmployeeId, array $changes): array
    {
        if ($changes === []) {
            throw new \InvalidArgumentException('Нет изменений для аудита.');
        }

        $row = [
            'id' => $this->nextId++,
            'order_id' => $orderId,
            'author_employee_id' => $authorEmployeeId !== null && $authorEmployeeId > 0
                ? $authorEmployeeId
                : null,
            'changes' => array_values($changes),
            'created_at' => (new DateTimeImmutable)->format(DATE_ATOM),
        ];
        $this->rows[] = $row;

        return $row;
    }

    public function listByOrderId(int $orderId): array
    {
        $matched = array_values(array_filter(
            $this->rows,
            static fn (array $row): bool => $row['order_id'] === $orderId,
        ));
        usort(
            $matched,
            static fn (array $a, array $b): int => $b['id'] <=> $a['id'],
        );

        return $matched;
    }
}
