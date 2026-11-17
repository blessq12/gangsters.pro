<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Repository\ClientRepository;

final class GetClientProfileUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        return $this->presenter->present($client);
    }
}
