<?php

namespace App\Application\Client\Query;

use App\Application\Client\Presenter\AdminClientPresenter;
use App\Domain\Supporting\Client\Port\ClientOrdersPort;
use App\Domain\Supporting\Client\Repository\ClientRepository;

final class GetAdminClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientOrdersPort $clientOrders,
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
            $this->clientOrders->listByClientId($clientId),
        );
    }
}
