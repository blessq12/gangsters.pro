<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Core\Order\Repository\OrderNoteRepository;
use App\Domain\Supporting\Staff\Enum\StaffRole;
use App\Infrastructure\Order\Model\OrderNoteModel;
use App\Infrastructure\Staff\Model\EmployeeModel;
use DateTimeImmutable;
use Illuminate\Support\Collection;

final class EloquentOrderNoteRepository implements OrderNoteRepository
{
    public function listByOrderId(int $orderId): array
    {
        /** @var Collection<int, OrderNoteModel> $rows */
        $rows = OrderNoteModel::query()
            ->where('order_id', $orderId)
            ->orderByDesc('id')
            ->get();

        $authors = $this->loadAuthors(
            $rows->pluck('author_employee_id')->all(),
        );

        return $rows
            ->map(fn (OrderNoteModel $row): array => $this->present($row, $authors))
            ->all();
    }

    public function countByOrderId(int $orderId): int
    {
        if ($orderId < 1) {
            return 0;
        }

        return (int) OrderNoteModel::query()
            ->where('order_id', $orderId)
            ->count();
    }

    public function countsByOrderIds(array $orderIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $orderIds),
            static fn (int $id): bool => $id > 0,
        )));
        if ($ids === []) {
            return [];
        }

        $counts = [];
        foreach ($ids as $id) {
            $counts[$id] = 0;
        }

        $rows = OrderNoteModel::query()
            ->selectRaw('order_id, COUNT(*) as aggregate')
            ->whereIn('order_id', $ids)
            ->groupBy('order_id')
            ->get();

        foreach ($rows as $row) {
            $counts[(int) $row->order_id] = (int) $row->aggregate;
        }

        return $counts;
    }

    public function add(int $orderId, string $body, ?int $authorEmployeeId): array
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Текст пометки обязателен.');
        }

        $row = OrderNoteModel::query()->create([
            'order_id' => $orderId,
            'body' => $trimmed,
            'author_employee_id' => $authorEmployeeId !== null && $authorEmployeeId > 0
                ? $authorEmployeeId
                : null,
            'created_at' => new DateTimeImmutable,
        ]);

        $authors = $this->loadAuthors([$row->author_employee_id]);

        return $this->present($row, $authors);
    }

    /**
     * @param  list<mixed>  $employeeIds
     * @return array<int, array{name: string, role: string|null}>
     */
    private function loadAuthors(array $employeeIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $employeeIds),
            static fn (int $id): bool => $id > 0,
        )));
        if ($ids === []) {
            return [];
        }

        $map = [];
        $rows = EmployeeModel::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'role']);

        foreach ($rows as $row) {
            $role = StaffRole::tryFrom((string) $row->role);
            $map[(int) $row->id] = [
                'name' => trim((string) $row->name),
                'role' => $role?->value,
            ];
        }

        return $map;
    }

    /**
     * @param  array<int, array{name: string, role: string|null}>  $authors
     * @return array{
     *     id: int,
     *     body: string,
     *     author_employee_id: int|null,
     *     author_name: string|null,
     *     author_role: string|null,
     *     created_at: string
     * }
     */
    private function present(OrderNoteModel $row, array $authors): array
    {
        $createdAt = $row->created_at;
        $iso = $createdAt instanceof \DateTimeInterface
            ? $createdAt->format(DATE_ATOM)
            : (new DateTimeImmutable)->format(DATE_ATOM);

        $authorId = $row->author_employee_id !== null
            ? (int) $row->author_employee_id
            : null;
        $author = $authorId !== null ? ($authors[$authorId] ?? null) : null;
        $authorName = is_array($author) ? trim((string) ($author['name'] ?? '')) : '';
        $authorRole = is_array($author) ? ($author['role'] ?? null) : null;

        return [
            'id' => (int) $row->id,
            'body' => (string) $row->body,
            'author_employee_id' => $authorId,
            'author_name' => $authorName !== '' ? $authorName : null,
            'author_role' => is_string($authorRole) && $authorRole !== '' ? $authorRole : null,
            'created_at' => $iso,
        ];
    }
}
