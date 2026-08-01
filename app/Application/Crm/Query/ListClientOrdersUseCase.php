<?php

namespace App\Application\Crm\Query;

use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;

final class ListClientOrdersUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $orderPresenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(int $clientId): array
    {
        return array_map(
            fn (Order $order): array => $this->orderPresenter->present($order),
            $this->orders->listByClientId($clientId),
        );
    }
}
