<?php

namespace App\Domain\Marketing\Repository;

use App\Domain\Marketing\Entity\StorefrontDisplayPrice;

interface StorefrontDisplayPriceRepository
{
    /**
     * @param  list<int>  $ids  Идентификаторы позиций каталога
     * @return array<int, StorefrontDisplayPrice> Ключ — catalogItemId
     */
    public function findByCatalogItemIds(array $ids): array;
}
