<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\Promotion;
use App\Domain\Content\Repository\PromotionRepository;
use App\Infrastructure\Content\Mapper\PromotionMapper;
use App\Infrastructure\Content\Model\MKT_Promotion;

final class EloquentPromotionRepository implements PromotionRepository
{
    public function __construct(
        private readonly PromotionMapper $mapper,
    ) {}

    public function findActiveOrdered(): array
    {
        return MKT_Promotion::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MKT_Promotion $row) => $this->mapper->toDomain($row))
            ->all();
    }
}
