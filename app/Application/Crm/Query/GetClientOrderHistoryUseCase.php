<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\ClientOrderHistoryPresenter;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;

/**
 * История заказов клиента (CRM): чтение снимков заказов по client_id.
 */
final class GetClientOrderHistoryUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly ClientOrderHistoryPresenter $presenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(int $clientId): array
    {
        return array_map(
            fn (Order $order): array => $this->presenter->present($order),
            $this->orders->listByClientId($clientId),
        );
    }
}
