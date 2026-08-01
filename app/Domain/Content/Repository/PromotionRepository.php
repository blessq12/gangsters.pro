<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\Promotion;

interface PromotionRepository
{
    /**
     * @return list<Promotion>
     */
    public function findActiveOrdered(): array;
}
