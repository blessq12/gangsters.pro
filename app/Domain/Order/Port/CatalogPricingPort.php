<?php

namespace App\Domain\Order\Port;

interface CatalogPricingPort
{
    public function findActiveProductQuote(int $productId): ?ProductPriceQuote;
}
