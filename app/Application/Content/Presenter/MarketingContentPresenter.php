<?php

namespace App\Application\Content\Presenter;

use App\Domain\Content\Entity\Banner;
use App\Domain\Content\Entity\Promotion;

final class MarketingContentPresenter
{
    /**
     * @param  list<Banner>  $banners
     * @return list<array<string, mixed>>
     */
    public function presentBanners(array $banners): array
    {
        return array_map(
            fn (Banner $banner): array => $this->presentBanner($banner),
            $banners,
        );
    }

    /**
     * @param  list<Promotion>  $promotions
     * @return list<array<string, mixed>>
     */
    public function presentPromotions(array $promotions): array
    {
        return array_map(
            fn (Promotion $promotion): array => $this->presentPromotion($promotion),
            $promotions,
        );
    }

    /**
     * @param  list<Banner>  $banners
     * @param  list<Promotion>  $promotions
     * @return array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>}
     */
    public function present(array $banners, array $promotions): array
    {
        return [
            'banners' => $this->presentBanners($banners),
            'promotions' => $this->presentPromotions($promotions),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentBanner(Banner $banner): array
    {
        return [
            'id' => $banner->id(),
            'image_desktop' => $banner->imageDesktop(),
            'image_mobile' => $banner->imageMobile(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentPromotion(Promotion $promotion): array
    {
        $body = $promotion->body();

        return [
            'id' => $promotion->id(),
            'title' => $promotion->title(),
            'image' => $promotion->image(),
            'body' => $body,
            'description' => $this->plainTextExcerpt($body),
        ];
    }

    private function plainTextExcerpt(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= 240) {
            return $text;
        }

        return mb_substr($text, 0, 237).'…';
    }
}
