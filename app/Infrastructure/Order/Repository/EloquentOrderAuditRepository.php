<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Core\Order\Repository\OrderAuditRepository;
use App\Infrastructure\Order\Model\OrderAuditModel;
use DateTimeImmutable;

final class EloquentOrderAuditRepository implements OrderAuditRepository
{
    public function add(int $orderId, ?int $authorEmployeeId, array $changes): array
    {
        if ($changes === []) {
            throw new \InvalidArgumentException('Нет изменений для аудита.');
        }

        $row = OrderAuditModel::query()->create([
            'order_id' => $orderId,
            'author_employee_id' => $authorEmployeeId !== null && $authorEmployeeId > 0
                ? $authorEmployeeId
                : null,
            'changes' => array_values($changes),
            'created_at' => new DateTimeImmutable,
        ]);

        return $this->present($row);
    }

    public function listByOrderId(int $orderId): array
    {
        return OrderAuditModel::query()
            ->where('order_id', $orderId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrderAuditModel $row): array => $this->present($row))
            ->all();
    }

    /**
     * @return array{id: int, order_id: int, author_employee_id: int|null, changes: list<array{field: string, old: mixed, new: mixed}>, created_at: string}
     */
    private function present(OrderAuditModel $row): array
    {
        $createdAt = $row->created_at;
        $iso = $createdAt instanceof \DateTimeInterface
            ? $createdAt->format(DATE_ATOM)
            : (new DateTimeImmutable)->format(DATE_ATOM);

        /** @var list<array{field: string, old: mixed, new: mixed}> $changes */
        $changes = is_array($row->changes) ? array_values($row->changes) : [];

        return [
            'id' => (int) $row->id,
            'order_id' => (int) $row->order_id,
            'author_employee_id' => $row->author_employee_id !== null
                ? (int) $row->author_employee_id
                : null,
            'changes' => $changes,
            'created_at' => $iso,
        ];
    }
}
