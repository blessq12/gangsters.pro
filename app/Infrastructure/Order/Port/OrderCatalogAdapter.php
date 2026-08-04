<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Order\Port\OrderCatalogPort;

final class OrderCatalogAdapter implements OrderCatalogPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    public function findActiveProductsByIds(array $ids): array
    {
        return array_map(
            fn (Product $product): array => $this->mapProduct($product),
            $this->catalogItems->findActiveProductsByIds($ids),
        );
    }

    public function findActiveSetsByIds(array $ids): array
    {
        return array_map(
            fn (ProductSet $set): array => $this->mapSet($set),
            $this->catalogItems->findActiveSetsByIds($ids),
        );
    }

    public function findActiveSystemProducts(): array
    {
        return array_map(
            fn (Product $product): array => $this->mapProduct($product),
            $this->catalogItems->findActiveSystemProducts(),
        );
    }

    public function findActiveComplementSetProducts(): array
    {
        return array_map(
            fn (Product $product): array => $this->mapProduct($product),
            $this->catalogItems->findActiveComplementSetProducts(),
        );
    }

    public function findProductById(int $id): ?array
    {
        $product = $this->catalogItems->findProductById($id);

        return $product instanceof Product ? $this->mapProduct($product) : null;
    }

    public function findPromotionMetaByProductIds(array $ids): array
    {
        return $this->catalogItems->findPromotionMetaByProductIds($ids);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProduct(Product $product): array
    {
        $imagePaths = [];
        foreach ($product->images() as $image) {
            $path = $image->path();
            if ($path === '') {
                continue;
            }

            $imagePaths[] = $path;
        }

        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'sku' => $product->sku(),
            'price_rubles' => $product->price()->amountRubles(),
            'is_active' => $product->isActive(),
            'is_system' => $product->isSystem(),
            'ingredients' => $product->ingredients(),
            'image_paths' => $imagePaths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSet(ProductSet $set): array
    {
        $lines = [];
        foreach ($set->lines() as $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'quantity' => $line->quantity(),
            ];
        }

        return [
            'id' => $set->id(),
            'name' => $set->name(),
            'sku' => $set->sku(),
            'price_rubles' => $set->price()->amountRubles(),
            'lines' => $lines,
        ];
    }
}
