<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Core\Order\Repository\OrderNoteRepository;
use App\Infrastructure\Order\Model\ORD_OrderNote;
use DateTimeImmutable;

final class EloquentOrderNoteRepository implements OrderNoteRepository
{
    public function listByOrderId(int $orderId): array
    {
        return ORD_OrderNote::query()
            ->where('order_id', $orderId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (ORD_OrderNote $row): array => $this->present($row))
            ->all();
    }

    public function add(int $orderId, string $body, ?int $authorEmployeeId): array
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Текст пометки обязателен.');
        }

        $row = ORD_OrderNote::query()->create([
            'order_id' => $orderId,
            'body' => $trimmed,
            'author_employee_id' => $authorEmployeeId !== null && $authorEmployeeId > 0
                ? $authorEmployeeId
                : null,
            'created_at' => new DateTimeImmutable,
        ]);

        return $this->present($row);
    }

    /**
     * @return array{id: int, body: string, author_employee_id: int|null, created_at: string}
     */
    private function present(ORD_OrderNote $row): array
    {
        $createdAt = $row->created_at;
        $iso = $createdAt instanceof \DateTimeInterface
            ? $createdAt->format(DATE_ATOM)
            : (new DateTimeImmutable)->format(DATE_ATOM);

        return [
            'id' => (int) $row->id,
            'body' => (string) $row->body,
            'author_employee_id' => $row->author_employee_id !== null
                ? (int) $row->author_employee_id
                : null,
            'created_at' => $iso,
        ];
    }
}
