<?php

namespace App\Application\Order\Query;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Order\Repository\OrderRepository;

final class ListAdminOrdersUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(?string $status = null, int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);
        $statusFilter = $status !== null && trim($status) !== '' ? trim($status) : null;

        return PageParams::wrap(
            $this->presenter->presentList(
                $this->orders->listPage($offset, $limit, $statusFilter),
            ),
            $this->orders->count($statusFilter),
            $offset,
            $limit,
        );
    }
}
