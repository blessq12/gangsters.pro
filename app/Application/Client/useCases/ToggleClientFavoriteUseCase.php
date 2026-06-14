<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\ToggleClientFavoriteDto;
use App\Application\Client\Presenter\ClientFavoritesPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientId;

final class ToggleClientFavoriteUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFavoritesPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ToggleClientFavoriteDto $input): array
    {
        $client = $this->findClient($input->clientId);

        $client->toggleFavorite(
            productId: $input->productId,
            productName: $input->productName,
            priceRub: $input->priceRub,
            weight: $input->weight,
        );

        $this->clients->save($client);

        return $this->presenter->present($client);
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
