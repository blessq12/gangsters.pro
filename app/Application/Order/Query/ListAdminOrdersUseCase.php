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
    public function execute(
        ?string $status = null,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
        ?string $q = null,
        ?int $branchId = null,
        ?string $deliveryMethod = null,
    ): array {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        $filters = [
            'status' => $status !== null && trim($status) !== '' ? trim($status) : null,
            'q' => $q !== null && trim($q) !== '' ? trim($q) : null,
            'branch_id' => $branchId !== null && $branchId > 0 ? $branchId : null,
            'delivery_method' => $deliveryMethod !== null && trim($deliveryMethod) !== ''
                ? trim($deliveryMethod)
                : null,
        ];

        return PageParams::wrap(
            $this->presenter->presentList(
                $this->orders->listPage($offset, $limit, $filters),
            ),
            $this->orders->count($filters),
            $offset,
            $limit,
        );
    }
}
