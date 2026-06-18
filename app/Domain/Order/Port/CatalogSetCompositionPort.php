<?php

namespace App\Domain\Order\Port;

interface CatalogSetCompositionPort
{
    /**
     * @param  list<int>  $setIds
     * @return array<int, list<CatalogSetCompositionLine>>
     */
    public function findActiveCompositionsBySetIds(array $setIds): array;
}
