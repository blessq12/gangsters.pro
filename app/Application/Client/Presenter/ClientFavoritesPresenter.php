<?php

namespace App\Application\Client\Presenter;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientFavorite;

final class ClientFavoritesPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Client $client): array
    {
        return [
            'favorites' => array_map(
                fn (ClientFavorite $favorite): array => $this->presentFavorite($favorite),
                $client->favorites(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentFavorite(ClientFavorite $favorite): array
    {
        return [
            'productId' => $favorite->productId(),
            'productSnapshot' => [
                'id' => $favorite->productId(),
                'name' => $favorite->productName() ?? '',
                'price' => $favorite->priceRub() ?? 0,
                'weight' => $favorite->weight(),
            ],
        ];
    }
}
