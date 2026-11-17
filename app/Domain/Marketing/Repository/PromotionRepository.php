<?php

namespace App\Domain\Marketing\Repository;

use App\Domain\Marketing\Entity\Promotion;

interface PromotionRepository
{
    /**
     * @return list<Promotion>
     */
    public function findActiveOrdered(): array;

    /**
     * @return list<Promotion>
     */
    public function findAllOrdered(): array;

    public function save(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): Promotion;
}
