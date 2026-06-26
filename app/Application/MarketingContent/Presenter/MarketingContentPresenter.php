<?php

namespace App\Application\MarketingContent\Presenter;

use App\Domain\MarketingContent\Entity\Banner;
use App\Domain\MarketingContent\Entity\Promotion;
use App\Domain\MarketingContent\Port\MarketingMediaUrlPort;

final class MarketingContentPresenter
{
    public function __construct(
        private readonly MarketingMediaUrlPort $mediaUrls,
    ) {}

    /**
     * @param  list<Banner>  $banners
     * @param  list<Promotion>  $promotions
     * @return array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>}
     */
    public function present(array $banners, array $promotions): array
    {
        return [
            'banners' => array_map(
                fn (Banner $banner): array => $this->presentBanner($banner),
                $banners,
            ),
            'promotions' => array_map(
                fn (Promotion $promotion): array => $this->presentPromotion($promotion),
                $promotions,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentBanner(Banner $banner): array
    {
        return [
            'id' => $banner->id(),
            'image_desktop' => $this->mediaUrls->resolve($banner->imageDesktop()),
            'image_mobile' => $this->mediaUrls->resolve($banner->imageMobile()),
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
            'image' => $this->mediaUrls->resolve($promotion->image()),
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
