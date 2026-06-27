<?php

namespace App\Domain\Order\Port;

interface CatalogPricingPort
{
    /**
     * Любой активный товар или набор, включая системные товары (подарки, дополнения).
     */
    public function findActiveProductQuote(int $productId): ?ProductPriceQuote;

    /**
     * Товар или набор, доступные для пользовательской корзины (системные товары исключены).
     */
    public function findStorefrontProductQuote(int $productId): ?ProductPriceQuote;
}
