<?php

namespace App\Application\Marketing\Promotion\Query;

use App\Domain\SystemContent\Entity\Promotion;
use App\Domain\SystemContent\Repository\PromotionRepository;

final class GetAdminPromotionListQuery
{
    public function __construct(
        private readonly PromotionRepository $promotions,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return array_map(
            static fn (Promotion $promotion): array => [
                'id' => $promotion->id(),
                'title' => $promotion->title(),
                'description' => $promotion->description(),
                'image' => $promotion->imagePath(),
            ],
            $this->promotions->findAllOrdered(),
        );
    }
}
