<?php

namespace App\Domain\Checkout\Port;

interface CatalogPricingPort
{
    public function findActiveProductQuote(int $productId): ?ProductPriceQuote;
}
