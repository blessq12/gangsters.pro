<?php

namespace App\Domain\Supporting\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\TechCard;

interface TechCardRepository
{
    public function findByProductId(int $productId): ?TechCard;

    /**
     * @param  list<int>  $productIds
     * @return array<int, TechCard> product_id => TechCard
     */
    public function findByProductIds(array $productIds): array;

    /** Полная замена строк техкарты товара (пустые lines — удалить карту). */
    public function save(TechCard $techCard): void;

    public function deleteByProductId(int $productId): void;
}
