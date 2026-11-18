<?php

namespace App\Application\Order\Query;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Order\Repository\OrderRepository;

final class ListAdminOrdersUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(?string $status = null, int $offset = 0, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);
        $statusFilter = $status !== null && trim($status) !== '' ? trim($status) : null;

        return [
            'items' => $this->presenter->presentList(
                $this->orders->listPage($offset, $limit, $statusFilter),
            ),
            'total' => $this->orders->count($statusFilter),
            'offset' => $offset,
            'limit' => $limit,
        ];
    }
}
