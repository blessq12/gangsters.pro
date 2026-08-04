<?php

namespace App\Application\Crm\Presenter;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Port\CrmCatalogSnapshotsPort;

/**
 * Контракт избранного для FE (favoritesStore.applyServerSnapshot).
 */
final class ClientFavoritesPresenter
{
    public function __construct(
        private readonly CrmCatalogSnapshotsPort $catalogSnapshots,
    ) {}

    /**
     * @param  array<int, array{name?: string|null, price?: float|int|null, weight?: mixed}>  $snapshotOverrides
     * @return array{favorites: list<array{productId: int, productSnapshot: array{id: int, name: string, price: float|int, weight: mixed}}>}
     */
    public function present(Client $client, array $snapshotOverrides = []): array
    {
        $ids = $client->favoriteProductIds();
        $catalogById = $this->catalogSnapshots->snapshotsByIds($ids);

        $favorites = [];
        foreach ($ids as $productId) {
            $override = $snapshotOverrides[$productId] ?? [];
            $snapshot = $catalogById[$productId] ?? null;

            $name = is_string($override['name'] ?? null) && $override['name'] !== ''
                ? (string) $override['name']
                : ($snapshot['name'] ?? ('Товар #'.$productId));

            $price = array_key_exists('price', $override) && $override['price'] !== null
                ? (float) $override['price']
                : ($snapshot['price_rubles'] ?? 0);

            $weight = $override['weight'] ?? null;

            $favorites[] = [
                'productId' => $productId,
                'productSnapshot' => [
                    'id' => $productId,
                    'name' => $name,
                    'price' => $price,
                    'weight' => $weight,
                ],
            ];
        }

        return ['favorites' => $favorites];
    }
}
