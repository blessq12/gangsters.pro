<?php

namespace App\Infrastructure\MarketingContent\Repository;

use App\Domain\MarketingContent\Entity\Promotion;
use App\Domain\MarketingContent\Repository\PromotionRepository;
use App\Infrastructure\MarketingContent\Mapper\PromotionMapper;
use App\Infrastructure\MarketingContent\Model\MKT_Promotion;

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
