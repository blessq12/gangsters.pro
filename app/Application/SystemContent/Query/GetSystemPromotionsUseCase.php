<?php

namespace App\Application\SystemContent\Query;

use App\Domain\SystemContent\Entity\Promotion;
use App\Domain\SystemContent\Repository\PromotionRepository;
use App\Shared\SystemContent\MediaUrlResolver;

final class GetSystemPromotionsUseCase
{
    public function __construct(
        private readonly PromotionRepository $promotions,
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {
    }

    public function execute(): array
    {
        $items = $this->promotions->findAllOrdered();

        return [
            'data' => array_map(
                fn (Promotion $promotion) => [
                    'id' => $promotion->id(),
                    'title' => $promotion->title(),
                    'description' => $promotion->description(),
                    'image' => $this->mediaUrlResolver->resolve($promotion->imagePath()),
                ],
                $items,
            ),
        ];
    }
}

