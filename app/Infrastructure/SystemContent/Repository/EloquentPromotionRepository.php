<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Promotion as PromotionEntity;
use App\Domain\SystemContent\Repository\PromotionRepository;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;

final class EloquentPromotionRepository implements PromotionRepository
{
    public function findAllOrdered(): array
    {
        return SYS_Promotion::query()
            ->orderBy('id')
            ->get()
            ->map(
                static fn (SYS_Promotion $promotion) => new PromotionEntity(
                    id: (int) $promotion->id,
                    title: $promotion->title,
                    description: $promotion->description,
                    imagePath: $promotion->image,
                ),
            )
            ->all();
    }
}

