<?php

namespace App\Domain\Crm\Port;

interface CrmCatalogAvailabilityPort
{
    /**
     * Активные позиции каталога: id → 'product'|'set'.
     *
     * @param  list<int>  $ids
     * @return array<int, string>
     */
    public function activeCatalogKindsByIds(array $ids): array;
}
