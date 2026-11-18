<?php

namespace App\Application\Crm\Query;

use App\Domain\Crm\Repository\ClientRepository;

final class GetClientFavoritesUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    /**
     * Domain хранит только product_id (снимок name/price — с фронта при toggle).
     *
     * @return list<array{product_id: int}>
     */
    public function execute(int $clientId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $favorites = [];
        foreach ($client->favoriteProductIds() as $productId) {
            $favorites[] = ['product_id' => $productId];
        }

        return $favorites;
    }
}
