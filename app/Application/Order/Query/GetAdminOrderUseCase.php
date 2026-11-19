<?php

namespace App\Application\Order\Query;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Core\Order\Repository\OrderRepository;

final class GetAdminOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $orderId): array
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        return $this->presenter->present($order);
    }
}
