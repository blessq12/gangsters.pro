<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientFavoritesPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class RemoveClientFavoriteUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFavoritesPresenter $presenter,
    ) {}

    /**
     * @return array{favorites: list<array<string, mixed>>}
     */
    public function execute(int $clientId, int $productId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        $client->removeFavoriteProductId($productId);
        $this->clients->save($client);

        return $this->presenter->present($client);
    }
}
