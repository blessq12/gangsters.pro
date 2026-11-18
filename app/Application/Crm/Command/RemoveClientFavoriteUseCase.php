<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Repository\ClientRepository;

final class RemoveClientFavoriteUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId, int $productId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $client->removeFavoriteProductId($productId);
        $this->clients->save($client);

        return $this->presenter->present($client);
    }
}
