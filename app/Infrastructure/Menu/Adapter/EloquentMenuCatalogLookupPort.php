<?php

namespace App\Infrastructure\Menu\Adapter;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Menu\Port\MenuCatalogLookupPort;

/**
 * ACL: Menu → Catalog. Презентация товара здесь, не в Menu Application.
 */
final class EloquentMenuCatalogLookupPort implements MenuCatalogLookupPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalog,
        private readonly CatalogPresenter $presenter,
    ) {}

    public function productExists(int $productId): bool
    {
        return $this->catalog->findProductById($productId) !== null;
    }

    public function presentActiveProductsByIds(array $ids): array
    {
        $indexed = [];
        foreach ($this->catalog->findActiveProductsByIds($ids) as $product) {
            if (! $product instanceof Product) {
                continue;
            }
            $indexed[$product->id()] = $this->presenter->product($product);
        }

        return $indexed;
    }

    public function findProductNamesByIds(array $ids): array
    {
        return $this->catalog->findProductNamesByIds($ids);
    }

    public function findProductWeightsByIds(array $ids): array
    {
        $weights = [];
        foreach ($this->catalog->findActiveProductsByIds($ids) as $product) {
            if (! $product instanceof Product) {
                continue;
            }
            $weights[$product->id()] = $product->weight();
        }

        return $weights;
    }
}
