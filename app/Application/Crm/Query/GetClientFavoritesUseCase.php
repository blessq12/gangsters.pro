<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\ClientFavoritesPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class GetClientFavoritesUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFavoritesPresenter $presenter,
    ) {}

    /**
     * @return array{favorites: list<array<string, mixed>>}
     */
    public function execute(int $clientId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        return $this->presenter->present($client);
    }
}
