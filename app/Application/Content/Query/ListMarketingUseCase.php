<?php

namespace App\Application\Content\Query;

use App\Domain\Content\Repository\BannerRepository;
use App\Domain\Content\Repository\PromotionRepository;

final class ListMarketingUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly PromotionRepository $promotions,
    ) {}

    /**
     * @return array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        $banners = [];
        foreach ($this->banners->findAllOrdered() as $banner) {
            $banners[] = [
                'id' => $banner->id(),
                'image_desktop' => $banner->imageDesktop(),
                'image_mobile' => $banner->imageMobile(),
                'is_active' => $banner->isActive(),
            ];
        }

        $promotions = [];
        foreach ($this->promotions->findAllOrdered() as $promotion) {
            $promotions[] = [
                'id' => $promotion->id(),
                'title' => $promotion->title(),
                'body' => $promotion->body() ?? '',
                'image' => $promotion->image(),
                'is_active' => $promotion->isActive(),
            ];
        }

        return [
            'banners' => $banners,
            'promotions' => $promotions,
        ];
    }
}
