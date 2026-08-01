<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientFavoritesPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class MergeClientFavoritesUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFavoritesPresenter $presenter,
    ) {}

    /**
     * @param  list<array{product_id: int, product_name?: string|null, price_rub?: float|int|null, weight?: mixed}>  $items
     * @return array{favorites: list<array<string, mixed>>}
     */
    public function execute(int $clientId, array $items): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        $overrides = [];
        $productIds = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId < 1) {
                continue;
            }

            $productIds[] = $productId;
            $overrides[$productId] = [
                'name' => isset($item['product_name']) ? (string) $item['product_name'] : null,
                'price' => $item['price_rub'] ?? null,
                'weight' => $item['weight'] ?? null,
            ];
        }

        $client->mergeFavoriteProductIds($productIds);
        $this->clients->save($client);

        return $this->presenter->present($client, $overrides);
    }
}
