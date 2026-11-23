<?php

namespace App\Application\Client\Command;

use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Supporting\Client\Repository\ClientRepository;

final class MergeGuestFavoritesUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @param  list<int|array{product_id?: int}>  $guestFavorites
     * @return array<string, mixed>
     */
    public function execute(int $clientId, array $guestFavorites): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $ids = [];
        foreach ($guestFavorites as $item) {
            if (is_array($item)) {
                $ids[] = (int) ($item['product_id'] ?? 0);
            } else {
                $ids[] = (int) $item;
            }
        }

        $client->mergeFavoriteProductIds($ids);
        $this->clients->save($client);

        return $this->presenter->present($client);
    }
}
