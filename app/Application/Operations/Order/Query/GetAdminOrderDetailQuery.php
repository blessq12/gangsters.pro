<?php

namespace App\Application\Operations\Order\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Contracts\AdminOrderReadRepository;
use App\Application\Operations\Order\Presenter\AdminOrderPresenter;

final class GetAdminOrderDetailQuery
{
    public function __construct(
        private readonly AdminOrderReadRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {
    }

    public function execute(string $orderId): array
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new ApiException('Order not found.', 404);
        }

        return $this->presenter->presentDetail($order);
    }
}
