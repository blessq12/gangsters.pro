<?php

namespace App\Domain\Order\Port;

interface CatalogRollMetaPort
{
    /**
     * @param  list<int>  $productIds
     * @return array<int, bool>
     */
    public function countsAsRollByProductIds(array $productIds): array;
}
