<?php

namespace App\Application\Crm\Presenter;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Crm\Entity\Client;

/**
 * Контракт избранного для FE (favoritesStore.applyServerSnapshot).
 */
final class ClientFavoritesPresenter
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    /**
     * @param  array<int, array{name?: string|null, price?: float|int|null, weight?: mixed}>  $snapshotOverrides
     * @return array{favorites: list<array{productId: int, productSnapshot: array{id: int, name: string, price: float|int, weight: mixed}}>}
     */
    public function present(Client $client, array $snapshotOverrides = []): array
    {
        $ids = $client->favoriteProductIds();
        $catalogById = $this->indexCatalog($ids);

        $favorites = [];
        foreach ($ids as $productId) {
            $override = $snapshotOverrides[$productId] ?? [];
            $catalogItem = $catalogById[$productId] ?? null;

            $name = is_string($override['name'] ?? null) && $override['name'] !== ''
                ? (string) $override['name']
                : ($catalogItem?->name() ?? ('Товар #'.$productId));

            $price = array_key_exists('price', $override) && $override['price'] !== null
                ? (float) $override['price']
                : ($catalogItem !== null ? $catalogItem->price()->amountRubles() : 0);

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

    /**
     * @param  list<int>  $ids
     * @return array<int, Product|ProductSet>
     */
    private function indexCatalog(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $indexed = [];
        foreach ($this->catalogItems->findActiveProductsByIds($ids) as $product) {
            $indexed[$product->id()] = $product;
        }
        foreach ($this->catalogItems->findActiveSetsByIds($ids) as $set) {
            $indexed[$set->id()] = $set;
        }

        return $indexed;
    }
}
