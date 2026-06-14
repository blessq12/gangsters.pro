<?php

namespace App\Infrastructure\Checkout\Port;

use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\Port\ProductPriceQuote;

final class CatalogPricingAdapter implements CatalogPricingPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    public function findActiveProductQuote(int $productId): ?ProductPriceQuote
    {
        $product = $this->catalogItems->findProductById($productId);

        if ($product === null || ! $product->isActive()) {
            return null;
        }

        return new ProductPriceQuote(
            productId: $product->id(),
            productName: $product->name(),
            unitPrice: $product->price(),
        );
    }
}
