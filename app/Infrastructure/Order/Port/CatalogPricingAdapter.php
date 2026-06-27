<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Order\Port\CatalogPricingPort;
use App\Domain\Order\Port\ProductPriceQuote;

final class CatalogPricingAdapter implements CatalogPricingPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    public function findActiveProductQuote(int $productId): ?ProductPriceQuote
    {
        return $this->resolveQuote($productId, storefrontOnly: false);
    }

    public function findStorefrontProductQuote(int $productId): ?ProductPriceQuote
    {
        return $this->resolveQuote($productId, storefrontOnly: true);
    }

    private function resolveQuote(int $productId, bool $storefrontOnly): ?ProductPriceQuote
    {
        $product = $this->catalogItems->findProductById($productId);

        if ($product instanceof Product) {
            if (! $product->isActive()) {
                return null;
            }

            if ($storefrontOnly && $product->isSystem()) {
                return null;
            }

            return new ProductPriceQuote(
                productId: $product->id(),
                productName: $product->name(),
                unitPrice: $product->price(),
                sku: $product->sku(),
                catalogKind: 'product',
            );
        }

        $set = $this->catalogItems->findSetById($productId);

        if ($set instanceof ProductSet && $set->isActive()) {
            return new ProductPriceQuote(
                productId: $set->id(),
                productName: $set->name(),
                unitPrice: $set->price(),
                catalogKind: 'set',
            );
        }

        return null;
    }
}
