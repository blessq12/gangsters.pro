<?php

namespace App\Domain\Marketing\Port;

/**
 * Чтение каталога для витрины: только массивы, без сущностей Catalog.
 *
 * Снимок позиции: {id, name, price_rubles, is_active}
 */
interface MarketingCatalogPort
{
    /**
     * @param  list<int>  $ids
     * @return list<array<string, mixed>>
     */
    public function findSnapshotsByIds(array $ids): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(): array;
}
