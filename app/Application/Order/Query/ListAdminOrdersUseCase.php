<?php

namespace App\Application\Order\Query;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;

final class ListAdminOrdersUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array{orders: list<array<string, mixed>>, page: int, per_page: int, total: int, counts: array<string, int>}
     */
    public function execute(int $page, int $perPage, ?string $status = null): array
    {
        $status = $this->statusFilter($status);
        $perPage = max(1, min(100, $perPage));
        $total = $this->orders->count($status);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        $rows = [];
        foreach ($this->orders->listPage(($page - 1) * $perPage, $perPage, $status) as $order) {
            $rows[] = $this->presenter->present($order);
        }

        return [
            'orders' => $rows,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'counts' => $this->counts(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function counts(): array
    {
        $grouped = $this->orders->countByStatus();
        $counts = [];
        foreach (OrderStatus::cases() as $case) {
            $counts[$case->value] = (int) ($grouped[$case->value] ?? 0);
        }

        return $counts;
    }

    private function statusFilter(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return OrderStatus::tryFrom($status)?->value;
    }
}
