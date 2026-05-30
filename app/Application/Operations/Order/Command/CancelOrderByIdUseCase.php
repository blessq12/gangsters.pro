<?php

namespace App\Application\Operations\Order\Command;

use App\Application\Order\Contracts\CancelOrderContract;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final class CancelOrderByIdUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly CancelOrderContract $cancelOrder,
    ) {
    }

    public function execute(string $orderId): void
    {
        $order = $this->orders->getById($orderId);
        $this->cancelOrder->cancel($order);
    }
}
