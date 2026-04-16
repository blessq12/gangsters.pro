<?php

namespace App\Infrastructure\Order\Catalog;

use App\Application\Common\Exceptions\ApiException;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;

final class EloquentCatalogItemSnapshotProvider implements CatalogItemSnapshotProvider
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    public function getActiveSnapshotsByIds(array $productIds): array
    {
        $products = $this->products->findByIds($productIds);
        $snapshots = [];

        foreach ($products as $product) {
            if ($product->status() !== Product::STATUS_ACTIVE) {
                continue;
            }

            $price = $product->price();
            $id = $product->id();
            if ($id === null) {
                continue;
            }
            if ($price === null || $price <= 0) {
                throw new ApiException("Product has no price: {$id}");
            }

            $snapshots[$id] = [
                'id' => $id,
                'name' => $product->name(),
                'sku' => $product->articul() ?? (string) $id,
                'price' => $price,
                'media' => $this->mapMedia($product->images()),
            ];
        }

        return $snapshots;
    }

    /**
     * @param  \App\Domain\Product\Entity\ProductImage[]  $images
     * @return array<int, array<string, mixed>>
     */
    private function mapMedia(array $images): array
    {
        $out = [];
        foreach ($images as $img) {
            foreach ($img->variants() as $variant) {
                $out[] = [
                    'url' => $variant->path(),
                    'variant' => $variant->size(),
                ];
            }
        }

        return $out;
    }
}
