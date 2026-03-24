<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Promotion;

interface PromotionRepository
{
    /**
     * @return Promotion[]
     */
    public function findAllOrdered(): array;
}

