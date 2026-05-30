<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Promotion;

interface PromotionRepository
{
    /**
     * @return Promotion[]
     */
    public function findAllOrdered(): array;

    public function findById(int $id): ?Promotion;

    public function save(Promotion $promotion): Promotion;

    public function delete(int $id): void;
}

