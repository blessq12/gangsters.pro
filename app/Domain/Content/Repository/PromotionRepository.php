<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\Promotion;

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
