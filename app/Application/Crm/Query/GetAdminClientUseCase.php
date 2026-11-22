<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\AdminClientPresenter;
use App\Domain\Supporting\Customer\Port\CrmClientOrdersPort;
use App\Domain\Supporting\Customer\Repository\ClientRepository;

final class GetAdminClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly CrmClientOrdersPort $clientOrders,
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
