<?php

namespace App\Application\Client\useCases;

use App\Application\Client\Presenter\ClientFavoritesPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientId;

final class GetClientFavoritesUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFavoritesPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId): array
    {
        return $this->presenter->present($this->findClient($clientId));
    }

    private function findClient(int $clientId): Client
    {
        $client = $this->clients->findById(ClientId::fromInt($clientId));

        if ($client === null) {
            throw ClientNotFoundException::forId($clientId);
        }

        return $client;
    }
}
