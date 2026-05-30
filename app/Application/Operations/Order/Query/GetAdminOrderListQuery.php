<?php

namespace App\Application\Operations\Order\Query;

use App\Application\Operations\Order\Contracts\AdminOrderReadRepository;
use App\Application\Operations\Order\Presenter\AdminOrderPresenter;
use App\Domain\Order\Entities\Order;

final class GetAdminOrderListQuery
{
    public function __construct(
        private readonly AdminOrderReadRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {
    }

    public function execute(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $paymentStatus = null,
        ?int $clientId = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $result = $this->orders->paginate(
            status: $status,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            search: $search,
            paymentStatus: $paymentStatus,
            clientId: $clientId,
            page: $page,
            perPage: $perPage,
        );

        return [
            'items' => array_map(
                fn (Order $order) => $this->presenter->presentListItem($order),
                $result['items'],
            ),
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
