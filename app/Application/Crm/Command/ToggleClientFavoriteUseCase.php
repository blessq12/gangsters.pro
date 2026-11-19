<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\ToggleFavoriteDto;
use App\Domain\Supporting\Customer\Repository\ClientRepository;

final class ToggleClientFavoriteUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    /**
     * @return array{product_id: int, name: ?string, price_kopecks: ?int, added: bool}
     */
    public function execute(int $clientId, ToggleFavoriteDto $dto): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $wasFavorite = in_array($dto->productId, $client->favoriteProductIds(), true);
        $client->toggleFavoriteProductId($dto->productId);
        $this->clients->save($client);

        $added = ! $wasFavorite;

        return [
            'product_id' => $dto->productId,
            'name' => $dto->name,
            'price_kopecks' => $dto->priceKopecks,
            'added' => $added,
        ];
    }
}
