<?php

namespace App\Domain\MarketingContent\Repository;

use App\Domain\MarketingContent\Entity\Promotion;

interface PromotionRepository
{
    /**
     * @return list<Promotion>
     */
    public function findActiveOrdered(): array;
}
