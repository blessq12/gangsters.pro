<?php

namespace App\Application\Marketing\Promotion\Presenter;

use App\Domain\SystemContent\Entity\Promotion;
use App\Shared\SystemContent\MediaUrlResolver;

final class AdminPromotionPresenter
{
    public function __construct(
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function presentListItem(Promotion $promotion): array
    {
        return $this->presentDetail($promotion);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentDetail(Promotion $promotion): array
    {
        return [
            'id' => $promotion->id(),
            'title' => $promotion->title(),
            'description' => $promotion->description(),
            'image' => $promotion->imagePath(),
            'image_url' => $this->mediaUrlResolver->resolve($promotion->imagePath()),
        ];
    }
}
