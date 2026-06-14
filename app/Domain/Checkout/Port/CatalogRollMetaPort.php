<?php

namespace App\Domain\Checkout\Port;

interface CatalogRollMetaPort
{
    /**
     * @param  list<int>  $productIds
     * @return array<int, bool>
     */
    public function countsAsRollByProductIds(array $productIds): array;
}
