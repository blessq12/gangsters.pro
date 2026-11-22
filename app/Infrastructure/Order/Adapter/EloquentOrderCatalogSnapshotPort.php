<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

/**
 * ACL: Order читает снимок каталога через CatalogItemRepository в Infrastructure.
 */
final class EloquentOrderCatalogSnapshotPort implements OrderCatalogSnapshotPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalog,
    ) {}

    public function findActiveProductsByIds(array $ids): array
    {
        $indexed = [];
        foreach ($this->catalog->findActiveProductsByIds($ids) as $product) {
            $images = [];
            foreach ($product->images() as $image) {
                $images[] = $image->path();
            }

            $indexed[$product->id()] = [
                'id' => $product->id(),
                'name' => $product->name(),
                'sku' => $product->sku(),
                'is_roll' => $product->isRoll(),
                'is_complement' => $product->isComplement(),
                'images' => $images,
            ];
        }

        return $indexed;
    }

    public function findProductNamesByIds(array $ids): array
    {
        return $this->catalog->findProductNamesByIds($ids);
    }
}
