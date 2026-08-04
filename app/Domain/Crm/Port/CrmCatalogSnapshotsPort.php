<?php

namespace App\Domain\Crm\Port;

interface CrmCatalogSnapshotsPort
{
    /**
     * @param  list<int>  $ids
     * @return array<int, array{name: string, price_rubles: int}>
     */
    public function snapshotsByIds(array $ids): array;
}
