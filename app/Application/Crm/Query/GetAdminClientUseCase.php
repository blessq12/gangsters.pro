<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\AdminClientPresenter;
use App\Domain\Supporting\Customer\Repository\ClientRepository;
use App\Domain\Core\Order\Repository\OrderRepository;

final class GetAdminClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly OrderRepository $orders,
        private readonly AdminClientPresenter $presenter,
    ) {}

    /**
     * @return array{client: array<string, mixed>, orders: list<array<string, mixed>>}
     */
    public function execute(int $clientId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        return $this->presenter->presentWithOrders(
            $client,
            $this->orders->listByClientId($clientId),
        );
    }
}
