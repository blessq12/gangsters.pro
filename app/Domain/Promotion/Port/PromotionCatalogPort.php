<?php

namespace App\Domain\Promotion\Port;

/**
 * Чтение каталога для акций: только снимки-массивы, без сущностей Catalog.
 *
 * Снимок товара: {id: int, name: string, price_rubles: int (рубли), is_active: bool}
 */
interface PromotionCatalogPort
{
    /**
     * @param  list<int>  $ids
     * @return list<array{id: int, name: string, price_rubles: int, is_active: bool}>
     */
    public function findProductSnapshotsByIds(array $ids): array;

    /**
     * @return array{id: int, name: string, price_rubles: int, is_active: bool}|null
     */
    public function findProductSnapshotById(int $id): ?array;
}
