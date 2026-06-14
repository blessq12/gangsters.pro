<?php

namespace App\Application\Order\useCases;

use App\Application\Order\DTO\ListClientOrdersDto;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;

/**
 * Сценарий: список заказов авторизованного клиента (история в SPA).
 */
final class ListClientOrdersUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $orderPresenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(ListClientOrdersDto $input): array
    {
        return array_map(
            fn (Order $order): array => $this->orderPresenter->present($order),
            $this->orders->listByClientId($input->clientId),
        );
    }
}
