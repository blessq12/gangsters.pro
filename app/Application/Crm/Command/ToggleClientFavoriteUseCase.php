<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientFavoritesPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class ToggleClientFavoriteUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFavoritesPresenter $presenter,
    ) {}

    /**
     * @param  array{name?: string|null, price?: float|int|null, weight?: mixed}  $snapshot
     * @return array{favorites: list<array<string, mixed>>}
     */
    public function execute(int $clientId, int $productId, array $snapshot = []): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        $client->toggleFavoriteProductId($productId);
        $this->clients->save($client);

        $overrides = [];
        if ($snapshot !== []) {
            $overrides[$productId] = $snapshot;
        }

        return $this->presenter->present($client, $overrides);
    }
}
