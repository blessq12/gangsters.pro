<?php

namespace App\Application\Order\Query;

use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final class GetClientOrdersUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderPresenter $presenter,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(int $clientId): array
    {
        $orders = $this->orders->findByClientId($clientId);

        return array_map(
            fn ($order) => $this->presenter->present($order),
            $orders,
        );
    }
}

